
<img src="img/mineral.png"></img>
# Information Plugin
```YAML
name: MineRal
api: 5.0.0
version: 1.0.0
author: NoobMCGaming ( NoobLovePMMP )
language: VietNam
```
# Wiki For You :3
## How to set up starter item ?
In ```manager.yml```, you will see..
```YAML
worlds: []
```
You can add items using the following syntax:
```YAML
worlds:
- < world name >
```
Example:
```YAML
worlds:
- lobby
```
You can use ```{name}``` to use name of player in name of world

```YAML
price-to-upgrade: 20000
```
Amount to upgrade mineral warehouse per level

## What is command to use this plugin ?
```YAML
commands: /mineral
```

## The difference between MineRal plugins and my MineRal plugin
In this plugin you don't need to use ```FormAPI```, Because I have integrated formapi into the plugin, it means you do not need to install any libs or plugins related to ```FormAPI``` to work.
