import pandas as pd
import numpy as np
import json
import os
import sys
import matplotlib.pylab as plt
import matplotlib.patches as patches
from scipy.interpolate import griddata  # , interp2d
from imageio import imread
from PIL import Image
import mysql.connector
import time;
import io

sys.stdout = io.open(sys.stdout.fileno(), 'w', encoding='utf8')

from matplotlib.collections import PatchCollection

from scipy.spatial import Voronoi, voronoi_plot_2d
from shapely.geometry import LineString, Polygon
#import descartes

#toggles
drawLines = False
drawPoints = True
drawHex = True
drawVoro = True
drawNuke = True
drawNames = False
drawInactive = True
doOpacity = True
debug = False


from colour import Color

with open(os.path.dirname(__file__)+"/../foxhole-settings.json", 'r', encoding="utf-8") as jsonFile:
    settings = json.load(jsonFile)
    jsonFile.close()

mydb = mysql.connector.connect(
  host=settings['db_server'],
  user=settings['db_username'],
  passwd=settings['db_password'],
  database=settings['db_name']
)
imgSavePath = settings["path"]+'/images/'

with open(settings["path"]+"/_regions.json", 'r', encoding="utf-8") as jsonFile:
    regionlist = json.load(jsonFile)
    jsonFile.close()#could remove < 20

greenColors = list(Color("#6dcf53").range_to(Color("#24a800"),5))#dark to light
blueColors = list(Color("#6c8aeb").range_to(Color("#0037e6"),5))

mapSize = settings['mapSize']
worldSize = settings['worldSize']
gridInterval = settings['gridInterval']
outputImageSize = settings['outputImageSize']

"""check for argvs"""
argv_list = sys.argv[1:]
print(argv_list)
arg1 = False
if len(argv_list) > 0:
    arg1 = argv_list[0]

currentTime =  time.time()

os.makedirs(imgSavePath, exist_ok=True)

nuked = []
activeRegions = []


def init_region_dict(init_with_status_zeroes=False):

    regions = {}
    global nuked   
    global activeRegions
    
    mycursor = mydb.cursor( buffered=True , dictionary=True)
    mycursor.execute("SELECT regionId, mapName, dynamic FROM `warapi_dynamic` WHERE etag != -1")
# WHERE regionId = 41
    myresult = mycursor.fetchall()

    for row in myresult:    

        regionName = row["mapName"]
        
        if regionName in ['Conquest_Total']:
            continue           
        
        print(regionName)            
      
        _dynamic = json.loads(row["dynamic"])


        """construct 2d-data array"""
        _data = []
        _names = []
        _status = []
        _timers = []

        for mapItem in _dynamic['mapItems']:     


            if (mapItem['flags'] & 0x10 and mapItem['iconType'] != 37) or mapItem['iconType'] == 71:
                mapItem['regionId'] = row['regionId']
                mapItem['regionName'] = regionName
                nuked += [mapItem]
                
            if 'regionData' not in mapItem:
                continue
         #   print(mapItem)

            x_coord, y_coord = np.round(mapItem['regionData']['x'], 4), np.round(1-mapItem['regionData']['y'], 4)
            _names += [mapItem['name']]
            _data += [[x_coord, y_coord]]
            
            if 'timer' in mapItem and mapItem['timer']:
                _timers += [mapItem['timer']]
            else:
                _timers += [0]

            if mapItem['teamId'] == 'COLONIALS':
                _status += [1]
            elif mapItem['teamId'] == 'WARDENS':
                _status += [2]
            elif mapItem['iconType'] == 44:
                _status += [3]
            elif 'wasTeam' in mapItem and mapItem['wasTeam'] == 'COLONIALS':
                _status += [4]
            elif 'wasTeam' in mapItem and mapItem['wasTeam'] == 'WARDENS':
                _status += [5]
            else:
                _status += [0]

        _data = np.array(_data)
        _names = pd.Series(_names)
        _status = pd.Series(_status)
        _timers = pd.Series(_timers)

        _df = pd.DataFrame(_data)
        _df = pd.concat([_df, _status, _names, _timers], axis=1)
        _df.columns = ['x', 'y', 'Status', 'Name', 'Timer']  #index reference to columns below

        regions[regionName] = _df     
        
        regions[regionName]['world_y'], regions[regionName]['world_x'] = convert(regionName, regions[regionName]['x'], regions[regionName]['y'])
        activeRegions += [regionName]       

    return regions


def convert(regionName, x, y):
    """convert x,y coords from Hextiles to Worldmap coords"""
    
    bottomLeft = getWorldBottomLeft(regionlist[regionName]['grid']['x'], regionlist[regionName]['grid']['y'])
    
    xcoord = bottomLeft['x'] + (mapSize['x'] * x)
    ycoord = bottomLeft['y'] + (mapSize['y'] * y)

    return [ycoord, xcoord]

def getWorldBottomLeft(gridX, gridY):

    x = gridInterval['x']*gridX
    y = worldSize['y'] - ((gridInterval['y']*gridY) + mapSize['y'])#bottom is y 0

    return {'x': x, 'y': y}

def getWorldCenter(gridX, gridY):

    bottomLeft = getWorldBottomLeft(gridX, gridY)
    
    x = bottomLeft['x'] + (mapSize['x']/2)
    y = bottomLeft['y'] + (mapSize['y']/2)

    return {'x': x, 'y': y}

def drawOccupationMap():


    fig = plt.figure('Occupation by Faction', figsize=(outputImageSize['x']/100, outputImageSize['y']/100), frameon=False)

    ax = fig.add_axes([0, 0, 1, 1])    
    
    #temp todo

    for name, currentMap in worldMap.items():
        #print(name)
       
        item = getWorldCenter(regionlist[name]['grid']['x'], regionlist[name]['grid']['y'])
 
        #coords                                      
        _data = currentMap.values[:,[6,5]]    #index reference to columns
   
        hexPoints = np.array([[item['x']-mapSize['x']/2,item['y']] , [item['x']-mapSize['x']/4,item['y']+mapSize['y']/2] , [item['x']+mapSize['x']/4,item['y']+mapSize['y']/2] , [item['x']+mapSize['x']/2,item['y']] , [item['x']+mapSize['x']/4,item['y']-mapSize['y']/2] , [item['x']-mapSize['x']/4,item['y']-mapSize['y']/2] , [item['x']-mapSize['x']/2,item['y']] ])


           
        #plot voro
        points = np.append(_data, [[9999,9999], [-9999,9999], [9999,-9999], [-9999,-9999]], axis = 0)
        
        vor = Voronoi(points, qhull_options ='Qbb Qc Qx')      
        #ax.plot(vor.vertices[:,0], vor.vertices[:,1], 'go')#plot major points
        
        hexPoly = Polygon(hexPoints)        

        #print(vor.point_region)
       # print(vor.regions)
            
        for key, region in enumerate(vor.regions):                                              
                                                                                                
           if not -1 in region:                                                                 
                                                                                                
               #if debug:                                                                       
                   #print(region)                                                               
                                                                                                
               regionVoro = Polygon([vor.vertices[i] for i in region])                          
                                                                                                
               regionPoly = hexPoly.intersection(regionVoro)                                    
                                                                                                
                #plot trimmed region poly                                                       
               x,y = regionPoly.exterior.xy                                                     
              # print(key)                                                                      
               id = np.where(vor.point_region == key)[0][0]                                     
               status = currentMap.loc[id,'Status']                                             
               timer = int(currentMap.loc[id,'Timer'])                                          
               increment = 24*3600 # 1 day
               daysHeld = round((currentTime - timer) / increment)
               increments = min(daysHeld, 4)
               linewidth=1                                                                      
               zorder = 5     
               h=''                                                                  
                                                                                                
               if status == 2:                                                                  
                  color = blueColors[increments].hex #warden 003dff                             
                  edgecolor = color                                                             
               elif status == 1:                                                                
                  color = greenColors[increments].hex #collie 1bb500                            
                  edgecolor = color                                                             
               elif status == 3:                                                                
                  color = "#fb0600"#horde                                                       
                  edgecolor = color                                                             
               elif status == 4:                                                                
                  color = "#ffffff"#was collies/taken by wardens                                
                  edgecolor = '#1bb500'                                                         
                  linewidth=10                                                                  
                  zorder = 6       
                  h='++'                                                              
               elif status == 5:                                                                
                  color = "#ffffff"#was wardens/taken by collies                                
                  edgecolor = '#003dff'                                                         
                  linewidth=10                                                                  
                  zorder = 6 
                  h='++'                                                                 
               else:                                                                            
                  color = "#ffffff"#noot                                                        
                  edgecolor = color                                                             

               if drawPoints:
                   if status == 2:
                       dotcolor = 'blue'
                   elif status == 1:      
                       dotcolor = 'green'
                   ax.plot(_data[:,0], _data[:,1], color=dotcolor,linewidth=0, marker='o', zorder=100)#plot major marker points (towns)   

               linewidth=1
               if drawVoro:
                   if drawLines:
                       edgecolor='#000000'
                       linewidth=1
                   plt.fill(x,y, facecolor=color,edgecolor=edgecolor, hatch=h, zorder=zorder, linewidth=linewidth )



    for regionName in set(regionlist):
        if regionlist[regionName]['id'] < 20:
             continue
        item = getWorldCenter(regionlist[regionName]['grid']['x'], regionlist[regionName]['grid']['y'])
        hexPoints = np.array([[item['x']-mapSize['x']/2,item['y']] , [item['x']-mapSize['x']/4,item['y']+mapSize['y']/2] , [item['x']+mapSize['x']/4,item['y']+mapSize['y']/2] , [item['x']+mapSize['x']/2,item['y']] , [item['x']+mapSize['x']/4,item['y']-mapSize['y']/2] , [item['x']-mapSize['x']/4,item['y']-mapSize['y']/2] , [item['x']-mapSize['x']/2,item['y']] ])
        
        if regionName not in set(activeRegions) and drawInactive:
            ax.fill(hexPoints[:,0], hexPoints[:,1], color='#767575') #fill inactive regions
        if drawNames:
            ax.text(item['x'], item['y'], regionlist[regionName]['name'],color='#ffffff', zorder=100, fontsize=13, ha='center', va='center')           
        if drawHex:
            ax.plot(hexPoints[:,0], hexPoints[:,1], linewidth=1,color='#000000', zorder=100)   #plot hex border

    for nuke in nuked:        
        cords = convert(nuke['regionName'], nuke['x'], 1-nuke['y'])
        if drawNuke: 
            ax.plot(cords[1], cords[0], marker='o' ,color='red',markersize=15,markeredgecolor='white', zorder=100)
        
    ax.set_ylim(0, worldSize['y'])
    ax.set_xlim(0, worldSize['x'])
    ax.axis('off')


    fig.savefig(imgSavePath + 'WorldMapControl_test.webp', transparent=True, dpi=100)


    fig.clf()
    plt.close(fig)

    #transparency
    im_rgba = Image.open(imgSavePath + 'WorldMapControl_test.webp')    
    blank = Image.new("RGBA", im_rgba.size)
    final = Image.blend(blank, im_rgba , 0.6)
   #im_rgba.putalpha(180)
    if doOpacity:
        final.save(imgSavePath + 'WorldMapControl_test.webp')

worldMap = init_region_dict(init_with_status_zeroes=False)

if debug:
    print(worldMap)
    print("Nuked:")
    print(nuked)


drawOccupationMap()


