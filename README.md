#PrivilegePlayerGo!
一款可以让PocketMine服务端的玩家拥有特别权限的插件.

作者: 极致·人生

简介:
 -高精准的计时体系
  按照实际天数进行计算, 玩家在游戏时不会中途被移除特权玩家身份.

 -搭载智能保护系统
  有效防止特权玩家利用各种方法为普通玩家刷物品.

 -防粒子卡顿服务器
  拥有燃放烟花排队的体系, 多名特权玩家组团燃放烟花需要排队进行, 防止粒子过多造成卡顿.

 -自定义的功能配置
  配置文件可支持自定义插件功能以及特权玩家权限开关功能


指令:
 特权系统指令帮助: /pp help
 添加一名特权玩家: /pp add <玩家名称> <时间/天>
 移除一名特权玩家: /pp remove <玩家名称>
 查看特权玩家列表: /pp list
 切换我的游戏模式: /pp gm
 开启关闭飞行模式: /pp fly
 查看我的个人信息: /pp info
 释放特权玩家烟花: /pp fire
 取回我的生存背包: /pp bag


配置文件:
 "背包保存"=>"开"为开启, "关"为关闭
 "死亡掉落"=>"开"为开启, "关"为关闭
 "飞行模式"=>"开"为开启, "关"为关闭
 "切换模式"=>"开"为开启, "关"为关闭
 "燃放烟花"=>"开"为开启, "关"为关闭
 "发射倍数"=>指特权玩家拥有的燃放烟花次数, 当设为1时, 若玩家购买了30天的特权玩家, 则拥有30枚烟花; 当设为2时, 若玩家购买了30天的特权玩家, 则拥有60枚烟花.


项目地址: https://git.coding.net/HyperLife/PrivilegePlayerGo.git
(若你下载的是bin文件, 请将bin改为zip然后解压即可获得插件源码)
