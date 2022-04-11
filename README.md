# Smart crowd routing
For this project a [webtool](https://we12s034.ugent.be/crowd/) has been developed which implements smart crowd routing.
The tool allows you to click two points on the map (within the case study area of Aalst's city center). Next, it calculates two routes: one with length as weight and one with pedestrian counts as weight. Finally, both routes are shown on the map together with information on the difference in length and difference in crowdedness. Under the hood, the webtool runs this [python code](code_3.py). Crowd data is real-time fetched through the [Telraam](https://telraam.net) API and edges that do not have a Telraam count get a random number between mean-SD and mean+SD. In a next step, these random numbers will be replaced by the predictions of a graph convolutional network (GCN). 

For any questions, please contact laudcock.decock@ugent.be.
