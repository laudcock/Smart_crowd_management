import osmnx as ox #geocoderen
import geopandas as gpd
import pandas as pd
import numpy as np
import math
import networkx as nx #routering
import requests #API
import json #API response format
from shapely.geometry import mapping, shape, Point, Polygon, LineString #oa API response omzetten naar shape object
from scipy.spatial import distance #afstand tussen punten
import sys #argumenten doorgeven
import random #edges vullen met random getallen

#Function to import real-time crowd counts
def Telraamcounts(time='live',contents='minimal',area='4.01,50.92,4.07,50.96'):
    #connecting to telraam API
    param = {'time':time,'contents':contents,'area':area}
    r = requests.post('https://telraam-api.net/v1/reports/traffic_snapshot', headers={'X-Api-Key':'GQY2NW1bdYa6e0kZLHqX5aSA5zg9SdMzaGFUNjhJ'}, json=param)
    if r.status_code == requests.codes.ok:
        telraam = r.json()
    else:
        r.status_code  

    #reading telraam json object and building dictionairy
    d = {'pedestrian': [], 'geometry': []}
    for i in range(len(telraam['features'])):
        s = shape(telraam['features'][i]['geometry'])
        pedestrian = telraam['features'][i]['properties']['pedestrian']
        if pedestrian != '':
            pedestrian = float(pedestrian)
        else:
            pedestrian= np.nan
        d['pedestrian'].append(pedestrian)
        d['geometry'].append(s)
    #creating geodataframe of dictionairy
    gdfl = gpd.GeoDataFrame(d, crs="EPSG:4326").to_crs(epsg=3857)
    gdfl.head()  #gdfl = pedestrian count + geometry of linestring (telraam segment)

    # centerpunt of telraam segment
    d={'count':[],'geometry':[]}
    for rij in gdfl.iterrows():
        lijn = rij[1].geometry
        count = rij[1]['pedestrian']
        punt = lijn.interpolate(0.5, normalized=True)
        d['geometry'].append(punt)
        d['count'].append(count)
    gdfp = gpd.GeoDataFrame(d,crs={'init': 'epsg:3857'})
    gdfp = gdfp.to_crs(4326)
    return gdfp #gdfp = pedestrian count + geometry of centerpoint

#Function to import graph and fill in Telraam attribute of edges    
def Graph(gdfp, filepath="/home/laudcock/graaf/Aalst.graphml"):
    #load graph
    GA = ox.load_graphml(filepath)

    #Match telraam points with nearest edges of graph
    x = list(gdfp.geometry.apply(lambda p: p.x))
    y = list(gdfp.geometry.apply(lambda p: p.y))
    telraamedges = ox.nearest_edges(GA, x, y, return_dist=False)
    
    #fill in count attribute with random numbers between mean-SD and mean+SD
    minval = round(abs(gdfp['count'].mean() - gdfp['count'].std()))
    maxval = round(gdfp['count'].mean() + gdfp['count'].std())
    for edge in GA.edges:
        count = round(random.uniform(minval, maxval))
        GA[edge[0]][edge[1]][0]['count'] = count

    #overwrite count attribute with Telraam counts if they are known
    for i,edge in enumerate(telraamedges):
        if not np.isnan(gdfp.iloc[i,0]):
            GA[edge[0]][edge[1]][0]['count'] = gdfp.iloc[i,0]
    
    return GA

#Function to calculate 2 shortest paths
def CrowdRoute(x1,y1,x2,y2):
    gdfp = Telraamcounts()
    GA = Graph(gdfp)

    #find nearest node to origin and destination
    x = [x1,x2]
    y = [y1,y2]
    nodes = ox.nearest_nodes(GA, x, y, return_dist=False)
    orig = nodes[0]
    dest = nodes[1]
    
    #calculate shortest path with a different weight: length and count
    shortestpath = nx.shortest_path(GA, source=orig, target=dest, weight='length')
    crowdpath = nx.shortest_path(GA, source=orig, target=dest, weight='count')
    
    #calculate total weight of both paths
    shortdist=0
    shortcount=0
    for i in range(len(shortestpath)-1):
        count = GA[shortestpath[i]][shortestpath[i+1]][0]['count']
        length = GA[shortestpath[i]][shortestpath[i+1]][0]['length']
        shortdist+=length 
        shortcount+=count
    
    crowddist=0
    crowdcount=0
    for i in range(len(crowdpath)-1):
        count = GA[crowdpath[i]][crowdpath[i+1]][0]['count']
        length = GA[crowdpath[i]][crowdpath[i+1]][0]['length']
        crowddist+=length 
        crowdcount+=count
    
    #generate output
    output='{}#{}#{}#{}'.format(str(round(shortcount-crowdcount)),str(round(crowddist-shortdist)),str(shortestpath),str(crowdpath))
    return output



#Run function
print(CrowdRoute(float(sys.argv[1]), float(sys.argv[2]), float(sys.argv[3]), float(sys.argv[4])))