<?php

/*
  __   __                                        ______              __
  \ \  \ \                                      / _____\            / /
   \ \__\ \  __    __  _____   _____    ____   / / ____    _____   / /         
    \  ___ \ \ \  / / / ___ \ / ___ \  / ___\ / / /___ \  / ___ \ /_/
     \ \  \ \ \ \/ / / /__/ // _____/ / /     \ \____/ / / /__/ / __
      \_\  \_\ \  / / _____/ \______//_/       \______/  \_____/ /_/
              _/ / / /
             /__/ /_/

                      HyperGo!|Copyright © 保留所有权利
                           Powered By HyperGo!
                            author HyperLife
*/

namespace PrivilegePlayerGo;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\scheduler\PluginTask;

use pocketmine\math\Vector3;

use pocketmine\block\Block;

use pocketmine\item\Item;

//背包引用
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;

//事件引用
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\player\PlayerDeathEvent;

use pocketmine\event\Listener;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;

//命令引用
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;

//粒子引用
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\level\particle\Particle;
use pocketmine\level\particle\EntityFlameParticle;
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\Level;

class PrivilegePlayerGo extends PluginBase implements Listener{

 public function onEnable(){
  $this->getServer()->getPluginManager()->registerEvents($this,$this);
  
  //插件启动提示
  $this->getServer()->getLogger()->warning("§bPrivilegePlayerGo§6已成功在PHP版本为: §e".(PHP_VERSION)."§6的§e".(PHP_OS)."§6系统上启动.");
  
  //创建配置文件
  @mkdir($this->getDataFolder(),0777,true);
  @mkdir($this->getDataFolder()."PlayerData/",0777,true);
  
  //特权玩家列表
  $this->PPL=new Config($this->getDataFolder()."/PlayerData/PlayerList.yml",Config::YAML,array("玩家列表"=>array()));
  
  //特权时间数据
  $this->PPT=new Config($this->getDataFolder()."/PlayerData/PrivilegeTime.yml",Config::YAML,array());
  
  //特权烟花次数
  $this->PFN=new Config($this->getDataFolder()."/PlayerData/FireNumber.yml",Config::YAML,array());
  
  //玩家背包数据
  $this->PBD=new Config($this->getDataFolder()."/PlayerData/BackpackData.yml",Config::YAML,array());
  
  //创造方块数据
  $this->CBD=new Config($this->getDataFolder()."/PlayerData/BlockData.yml",Config::YAML,array("方块数据"=>array()));
  
  //特权权限设置
  $this->PFT=new Config($this->getDataFolder()."特权权限设置.yml",Config::YAML,array(
  "背包保存"=>"开",
  "死亡掉落"=>"关",
  "飞行模式"=>"开",
  "切换模式"=>"开",
  "燃放烟花"=>"开",
  "发射倍数"=>1
  ));
 }
 
 public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
 
  //指令纠错
  if($cmd->getName()=="pp"){
   if(isset($args[0])){
    if($args[0]!="add" AND $args[0]!="remove" AND $args[0]!="list" AND $args[0]!="info" AND $args[0]!="fire" AND $args[0]!="gm" AND $args[0]!="fly" AND $args[0]!="help" AND $args[0]!="bag"){
     $sender->sendMessage("§b=====特权玩家系统=====\n§6特权系统指令帮助: §e/pp help");
    }
   }
  }
 
  //添加特权玩家
  if($cmd->getName()=="pp"){
   if($sender->isOp()){
    if(isset($args[0])){
     if($args[0]=="add"){
      if(isset($args[1])){
       if(isset($args[2])){
        if(is_numeric($args[2])){
         if($args[2]!=0){
  	        switch($args[0]){
  	         case"add":
            if($this->PPL->exists($args[1])){
             $sender->sendMessage("§b=====特权玩家系统=====\n§6特权玩家§e{$args[1]}§6的特权时间尚未过期, 无法再次添加已有的特权玩家!");
            }
            else{
             $day=intval($args[2]);//字符串数据化
            
             $fireNumber=$day*$this->PFT->get("发射倍数");
            
             $addTime=time();//添加时的时间戳
             $getTime=$day*86400;//特权时间的时间戳
             $allTime=$addTime+$getTime;//总的时间戳
            
             $PP=$this->PPL->get("玩家列表");
            
             if(!in_array($args[1],$PP)){
									    $PP[]=$args[1];
									    $this->PPL->set("玩家列表",$PP);
									    $this->PPL->save();
             }
             $this->PPT->set($args[1],$allTime);
             $this->PPT->save();
            
             $this->PFN->set($args[1],$fireNumber);
             $this->PFN->save();
            
             $sender->sendMessage("§b=====特权玩家系统=====\n§6成功添加特权玩家: §e{$args[1]}§6, 特权时间为: §e{$day}§6天.");
             $this->getServer()->broadcastMessage("§b=====特权玩家系统=====\n§6恭喜玩家§e{$args[1]}§6成为一名新的特权玩家!");
            }
           break;
          
           default:
            $sender->sendMessage("§b=====特权玩家系统=====\n§6特权系统指令帮助: §e/pp help");
           break;
          }
         }
         else{
          $sender->sendMessage("§b=====特权玩家系统=====\n§6你不能添加一个特权时间为§e0§6天的特权玩家!");
         }
        }
        else{
         $sender->sendMessage("§b=====特权玩家系统=====\n§6请使用数字填写指令中的特权时间!");
        }
       }
       else{
        $sender->sendMessage("§b=====特权玩家系统=====\n§6特权系统指令帮助: §e/pp help");
       }
      }
      else{
       $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp add <玩家名称> <时间/天>");
      }
     }
    }
    else{
     $sender->sendMessage("§b=====特权玩家系统=====\n§6特权系统指令帮助: §e/pp help");
    }
   }
  }
 
  if($cmd->getName()=="pp"){
   if(isset($args[0])){
    if(isset($args[1])==false){
     if(isset($args[2])==false){
      if($sender->isOp()){
       
       $senderName=$sender->getName();
      
       $GMSwitch=$this->PFT->get("切换模式");
       $FlySwitch=$this->PFT->get("飞行模式");
       $FireSwitch=$this->PFT->get("燃放烟花");
       
       $fireNumber=$this->PFN->get($senderName);
       
       switch($args[0]){
        case"help":
         $sender->sendMessage("§b=====特权玩家系统=====\n§6特权系统指令帮助: §e/pp help\n§6添加一名特权玩家: §e/pp add <玩家名称> <时间/天>\n§6移除一名特权玩家: §e/pp remove <玩家名称>\n§6查看特权玩家列表: §e/pp list\n§6切换我的游戏模式: §e/pp gm\n§6开启关闭飞行模式: §e/pp fly\n§6查看我的个人信息: §e/pp info\n§6释放特权玩家烟花: §e/pp fire");
        break;
       
        case"list":
         $list=$this->PPL->get("玩家列表");
         $ppl=implode(", ",$list);
         $sender->sendMessage("§b=====特权玩家系统=====\n§6特权玩家列表: §e{$ppl}");
        break;
       }
       
       if($this->PPT->exists($senderName)){
        switch($args[0]){
         case"gm":
          if($GMSwitch=="开"){
           if($sender instanceof Player){
            $senderName=$sender->getName();
            if($this->PPT->exists($senderName)){
             $nowGM=$sender->getGamemode();
             if($nowGM==0 AND $nowGM!=1 AND $nowGM!=2 AND $nowGM!=3){
              $sender->setGamemode(1);
              $sender->sendMessage("§b=====特权玩家系统=====\n§6你的游戏模式已切换为: §e创造模式");
             }
             if($nowGM==1 AND $nowGM!=0 AND $nowGM!=2 AND $nowGM!=3){
              $sender->setGamemode(0);
              $sender->sendMessage("§b=====特权玩家系统=====\n§6你的游戏模式已切换为: §e生存模式");
             }
            }
           }
           else{
            $sender->sendMessage("§b=====特权玩家系统=====\n§6请在游戏中使用此指令!");
           }
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6管理员尚未开放切换模式权限");
          }
         break;
         
         case "fly":
          if($FlySwitch=="开"){
           $senderName=$sender->getName();
           if($this->PPT->exists($senderName)){
            if($sender->getAllowFlight()){
             $sender->setAllowFlight(false);
             $sender->sendMessage("§b=====特权玩家系统=====\n§6飞行模式已关闭");
            }
            else{
             if(!$sender->getAllowFlight()){
              $sender->setAllowFlight(true);
              $sender->sendMessage("§b=====特权玩家系统=====\n§6飞行模式已开启");
             }
            }
           }
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6管理员尚未开放飞行模式权限");
          }
         break;
        
         case"fire":
          if($FireSwitch=="开"){
           $number=$fireNumber-1;
           $this->PFN->set($senderName,$number);
           $this->PFN->save();
           
           if($fireNumber>0){
            $this->getServer()->broadcastMessage("§b=====特权玩家系统=====\n§6特权玩家§e{$senderName}§6即将燃放烟花, 请注意观赏!");
            $sender->sendTip("§6燃放烟花准备, 倒计时: §e5\n\n\n");
            sleep(1);
            $sender->sendTip("§6燃放烟花准备, 倒计时: §e4\n\n\n");
            sleep(1);
            $sender->sendTip("§6燃放烟花准备, 倒计时: §e3\n\n\n");
            sleep(1);
            $sender->sendTip("§6燃放烟花准备, 倒计时: §e2\n\n\n");
            sleep(1);
            $sender->sendTip("§6燃放烟花准备, 倒计时: §e1\n\n\n");
            sleep(1);
            $sender->sendMessage("§b=====特权玩家系统=====\n§6你成功燃放了一枚烟花!");
            //获取玩家坐标
            $x1=$sender->getX();
            $y1=$sender->getY();
            $z1=$sender->getZ();

            $level = $sender->getLevel();
            //y轴提升动画效果
            for($i = 0;$i < 25;$i++){
             $level->addParticle(new LavaDripParticle(new Vector3($x1,$y1 + $i,$z1)));
             for($i1 = 0;$i1 <= 180;$i1 += 20){
              $pz = $z1 + (7 * cos(deg2rad($i1)));
              for($i2 = 0;$i2 < 360;$i2 += 20){
               $level->addParticle(new EntityFlameParticle(new Vector3($x1 + 7 * sin(deg2rad($i1)) * cos(deg2rad($i2)),$y1 + 25 + 7 * sin(deg2rad($i1)) * sin(deg2rad($i2)),$pz,245,110,0)));
              }
             }
            }
           }
           else{
            $sender->sendMessage("§b=====特权玩家系统=====\n§6你的烟花已全部用完!");
           }
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6管理员尚未开放燃放烟花权限");
          }
         break;
         
         case"info":
          $senderName=$sender->getName();
          $nowTime=time();//获取当前的时间戳
          $allTime=$this->PPT->get($senderName);//获取存储于配置文件中的总时间戳
          $time=($allTime-$nowTime)/86400;
          $haveTime=ceil($time);//取整
          $sender->sendMessage("§b=====特权玩家系统=====\n§6我的特权时间还剩§e{$haveTime}§6天.\n§6我的特权烟花还剩§e{$fireNumber}§6枚.");
         break;
        }
       }
      }
      //如果发送者不是op是VIP
      else{
       
       $senderName=$sender->getName();
       
       $GMSwitch=$this->PFT->get("切换模式");
       $FlySwitch=$this->PFT->get("飞行模式");
       $FireSwitch=$this->PFT->get("燃放烟花");
       $fireNumber=$this->PFN->get($senderName);
       
       if($this->PPT->exists($senderName)){
        switch($args[0]){
         case"help":
          $sender->sendMessage("§b=====特权玩家系统=====\n§6特权系统指令帮助: §e/pp help\n§6查看特权玩家列表: §e/pp list\n§6切换我的游戏模式: §e/pp gm\n§6开启关闭飞行模式: §e/pp fly\n§6查看我的个人信息: §e/pp info\n§6释放特权玩家烟花: §e/pp fire");
         break;
       
         case"list":
          $list=$this->PPL->get("玩家列表");
          $ppl=implode(", ",$list);
          $sender->sendMessage("§b=====特权玩家系统=====\n§6特权玩家列表: §e{$ppl}");
         break;
       
         case"info":
          $senderName=$sender->getName();
          $nowTime=time();//获取当前的时间戳
          $allTime=$this->PPT->get($senderName);//获取存储于配置文件中的总时间戳
          $time=($allTime-$nowTime)/86400;
          $haveTime=ceil($time);//取整
          $sender->sendMessage("§b=====特权玩家系统=====\n§6我的特权时间还剩§e{$haveTime}§6天.\n§6我的特权烟花还剩§e{$fireNumber}§6枚.");
         break;
        
         case"gm":
          if($GMSwitch=="开"){
           if($sender instanceof Player){
            $nowGM=$sender->getGamemode();
            if($nowGM==0 AND $nowGM!=1 AND $nowGM!=2 AND $nowGM!=3){
             $sender->setGamemode(1);
             $sender->sendMessage("§b=====特权玩家系统=====\n§6你的游戏模式已切换为: §e创造模式");
            }
            if($nowGM==1 AND $nowGM!=0 AND $nowGM!=2 AND $nowGM!=3){
             $sender->setGamemode(0);
             $sender->sendMessage("§b=====特权玩家系统=====\n§6你的游戏模式已切换为: §e生存模式");
            }
           }
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6管理员尚未开放切换模式权限");
          }
         break;
         
         case "fly":
          if($FlySwitch=="开"){
           if($sender instanceof Player){
            $senderName=$sender->getName();
            if($this->PPT->exists($senderName)){
             if($sender->getAllowFlight()){
              $sender->setAllowFlight(false);
              $sender->sendMessage("§b=====特权玩家系统=====\n§6飞行模式已关闭.");
             }
             else{
              if(!$sender->getAllowFlight()){
               $sender->setAllowFlight(true);
               $sender->sendMessage("§b=====特权玩家系统=====\n§6飞行模式已开启.");
              }
             }
            }
           }
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6管理员尚未开放飞行模式权限");
          }
         break;
         
         case"fire":
          if($FireSwitch=="开"){
           $number=$fireNumber-1;
           $this->PFN->set($senderName,$number);
           $this->PFN->save();
           
           if($fireNumber>0){
           $this->getServer()->broadcastMessage("§b=====特权玩家系统=====\n§6特权玩家§e{$senderName}§6即将燃放烟花, 请注意观赏!");
           $sender->sendTip("§6燃放烟花准备, 倒计时: §e5\n\n\n");
           sleep(1);
           $sender->sendTip("§6燃放烟花准备, 倒计时: §e4\n\n\n");
           sleep(1);
           $sender->sendTip("§6燃放烟花准备, 倒计时: §e3\n\n\n");
           sleep(1);
           $sender->sendTip("§6燃放烟花准备, 倒计时: §e2\n\n\n");
           sleep(1);
           $sender->sendTip("§6燃放烟花准备, 倒计时: §e1\n\n\n");
           sleep(1);
           $sender->sendMessage("§b=====特权玩家系统=====\n§6你成功燃放了一枚烟花!");
           //获取玩家坐标
           $x1=$sender->getX();
           $y1=$sender->getY();
           $z1=$sender->getZ();

           $level = $sender->getLevel();
           //y轴提升动画效果
           for($i = 0;$i < 25;$i++){
            $level->addParticle(new LavaDripParticle(new Vector3($x1,$y1 + $i,$z1)));
            for($i1 = 0;$i1 <= 180;$i1 += 20){
             $pz = $z1 + (7 * cos(deg2rad($i1)));
             for($i2 = 0;$i2 < 360;$i2 += 20){
              $level->addParticle(new EntityFlameParticle(new Vector3($x1 + 7 * sin(deg2rad($i1)) * cos(deg2rad($i2)),$y1 + 25 + 7 * sin(deg2rad($i1)) * sin(deg2rad($i2)),$pz,245,110,0)));
             }
            }
           }
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6你的烟花已全部用完!");
          }
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6管理员尚未开放燃放烟花权限");
          }
         break;
        }
       }
       else{
        $sender->sendMessage("§b=====特权玩家系统=====\n§6你还不是特权玩家!");
       }
      }
     }
    }
   }
  }
  
  //移除特权玩家
  if($cmd->getName()=="pp"){
   if(isset($args[0]) AND $args[0]!="add" AND $args[0]!="help" AND $args[0]!="list" AND $args[0]!="fire" AND $args[0]!="time" AND $args[0]!="gm" AND $args[0]!="fly" AND $args[0]!="bag"){
    if($args[0]=="remove"){
     if($sender->isOp()){
      if(isset($args[1]) AND $args[0]=="remove"){
       if($this->PPT->exists($args[1])){
       
        $this->PPT->remove($args[1]);
        $this->PPT->save();
        
        $this->PFN->remove($args[1]);
        $this->PFN->save();
        
        $PP=$this->PPL->get("玩家列表");
        $inv=array_search($args[1],$PP);
        $inv=array_splice($PP,$inv,1); 
        $this->PPL->set("玩家列表",$PP);
        $this->PPL->save();
        
        $sender->sendMessage("§b=====特权玩家系统=====\n§6成功移除特权玩家: §e$args[1]");
       }
       else{
        $sender->sendMessage("§b=====特权玩家系统=====\n§e{$args[1]}§6不是特权玩家, 无法移除!");
       }
      }
      else{
       $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp remove <玩家名称>");
      }
     }
    }
   }
  }
  
  //背包数据返回
  if($cmd->getName()=="pp"){
   if(isset($args[0]) AND $args[0]!="add" AND $args[0]!="help" AND $args[0]!="list" AND $args[0]!="fire" AND $args[0]!="time" AND $args[0]!="gm" AND $args[0]!="fly" AND $args[0]!="remove"){
    if($args[0]=="bag"){
     if($sender instanceof Player){
    
      $senderName=$sender->getName();
      $nowGM=$sender->getGamemode();
     
      if($this->PBD->exists($senderName)){
     
       $id=$this->PBD->get($senderName)["物品"];
       $ids=$this->PBD->get($senderName)["特殊"];
       $number=$this->PBD->get($senderName)["数量"];
 
       if($nowGM==0){
        foreach($sender->getInventory()->getContents() as $item){
         //判断格子内的物品是不是空气
         if($item->getId()!=0){
          $sender->sendMessage("§a=====智能保护系统=====\n§e你必须清空你的背包才能取回背包!");
          return true;
         }
         continue;
        }
      
        for($i=0;$i<count($id);$i++){
         $item = Item::get((int) $id[$i],(int)  $ids[$i],(int) $number[$i]);
         $sender->getInventory()->addItem($item);
        }
        $this->PBD->remove($senderName);
        $this->PBD->save();
        $sender->sendMessage("§a=====智能保护系统=====\n§e你已成功取回背包!");

       }
       else{
       $sender->sendMessage("§a=====智能保护系统=====\n§e你只能在生存模式下取回背包!");
       //return true;
       }
      }
      else{
       $sender->sendMessage("§a=====智能保护系统=====\n§e无法找到你的背包数据!");
      //return true;
      }
     }
    }
   }
  }
  //return true;
  
 }
 
 public function onPlayerJoin(PlayerJoinEvent $event){
  $player=$event->getPlayer();
   $playerName=$player->getName();
    if($this->PPT->exists($playerName)){
    
     $allTime=$this->PPT->get($playerName);
     $nowTime=time();
     
     if($allTime>=$nowTime){
      $player->getPlayer()->sendMessage("§b=====特权玩家系统=====\n§6亲爱的特权玩家§e{$playerName}§6, 欢迎回到服务器!");
      sleep(1);
      $this->getServer()->broadcastMessage("§b=====特权玩家系统=====\n§6欢迎特权玩家§e{$playerName}§6加入服务器!");
     }
     if($allTime<$nowTime){
      $player->getPlayer()->sendMessage("§b=====特权玩家系统=====\n§6你的特权玩家身份已过期!");
     
      $this->PPT->remove($playerName);
      $this->PPT->save();
      
      $this->PFN->remove($playerName);
      $this->PFN->save();
      
      $PP=$this->PPL->get("玩家列表");
      $inv=array_search($playerName,$PP);
      $inv=array_splice($PP,$inv,1); 
      $this->PPL->set("玩家列表",$PP);
      $this->PPL->save();
      
      $nowGM=$player->getGamemode();
      
      if($nowGM==1 AND $nowGM!=0 AND $nowGM!=2 AND $nowGM!=3){
       $player->setGamemode(0);
      }
      if($player->getAllowFlight()){
       $player->setAllowFlight(false);
      }
     }
    }
    else{
     $nowGM=$player->getGamemode();
      
     if($nowGM==1 AND $nowGM!=0 AND $nowGM!=2 AND $nowGM!=3){
      $player->setGamemode(0);
     }
     if($player->getAllowFlight()){
      $player->setAllowFlight(false);
     }
    }
 }
 
 public function onDropItem(PlayerDropItemEvent $event){
  $player=$event->getPlayer();
  if($event->getPlayer()->getGamemode(1)){
   $event->setCancelled(true);
   $player->sendMessage("§a=====智能保护系统=====\n§e为了不影响游戏的平衡性, 创造模式下禁止丢弃物品!");
  }
 }
 
 public function onTouch(PlayerInteractEvent $event){
  $player=$event->getPlayer();
  $playerName=$player->getName();
  $block=$event->getBlock();
  $id=$block->getId();
  if($id==54){
   $nowGM=$player->getGamemode();
   if($nowGM==1 AND $nowGM!=0 AND $nowGM!=2 AND $nowGM!=3){
    $event->setCancelled(true);
    $player->sendMessage("§a=====智能保护系统=====\n§e为了不影响游戏的平衡性, 创造模式下禁止使用箱子!");
   }
	 }
 }
 
 public function onModeChange(PlayerGameModeChangeEvent $event){
 
  $type=0;
  
  $BagSwitch=$this->PFT->get("背包保存");
  
  if($BagSwitch=="开"){
   $player=$event->getPlayer();
   $nowGM=$player->getGamemode();
   $playerName=$player->getName();
			
   if($nowGM==0){
    $id=array();
    $ids=array();
    $number=array();

    foreach($player->getInventory()->getContents() as $item){
     //判断格内的物品是否为空气
     if($item->getId()!=0){
 
      $type=1;
     
      //获取方块ID
      $_id=intval($item->getId());
      //获取方块特殊值
      $_ids=intval($item->getDamage());
      //获取方块数量
      $_number=intval($item->getCount());
      
      array_push($id,$_id);
      array_push($ids,$_ids);
      array_push($number,$_number);
     }
     else{

      $type=2;
     
     }
     continue;
    }
    //储存背包数据到配置文件
    if($type==1){
     if($this->PBD->exists($playerName)){
     
      $id_=$this->PBD->get($playerName)["物品"];
      $ids_=$this->PBD->get($playerName)["特殊"];
      $number_=$this->PBD->get($playerName)["数量"];
      
      //将旧数据和新数据整合到一起
      $_id_=array_merge($id_,$id);
      $_ids_=array_merge($ids_,$ids);
      $_number_=array_merge($number_,$number);
     
      $this->PBD->set($playerName,["物品"=>$_id_,"特殊"=>$_ids_,"数量"=>$_number_]);
      $this->PBD->save();
    
      $player->sendMessage("§a=====智能保护系统=====\n§e你在生存模式下的背包数据已保存, 请在生存模式下输入指令§f/pp bag§e来取回你的背包.");
     }
     else{
     
      $this->PBD->set($playerName,["物品"=>$id,"特殊"=>$ids,"数量"=>$number]);
      $this->PBD->save();
     
      $player->sendMessage("§a=====智能保护系统=====\n§e你在生存模式下的背包数据已保存, 请在生存模式下输入指令§f/pp bag§e来取回你的背包.");
     }
    }
   }
   if($nowGM==1 OR $nowGM==2 OR $nowGM==3){
    $player->sendMessage("§a=====智能保护系统=====\n§e你可以在生存模式下输入指令§f/pp bag§e来取回你的背包.");
   }
  }
 }
 
 //创造放置方块记录
 public function onPlaceBlock(BlockPlaceEvent $event){
  $mode=$event->getPlayer()->getGamemode();
  if($mode==1){
   //获取方块的坐标
   $x=$event->getBlock()->getX();
   $y=$event->getBlock()->getY();
   $z=$event->getBlock()->getZ();
   
   $xyz="{$x}:{$y}:{$z}";
   $block=$this->CBD->get("方块数据");
            
   if(!in_array($xyz,$block)){
			 $block[]=$xyz;
				$this->CBD->set("方块数据",$block);
				$this->CBD->save();
   }
  }
 }
 
 //破坏创造方块检测
 public function onBreakBlock(BlockBreakEvent $event){
  $mode=$event->getPlayer()->getGamemode();
  if(!$mode==1){
   //获取方块的坐标
   $x=$event->getBlock()->getX();
   $y=$event->getBlock()->getY();
   $z=$event->getBlock()->getZ();
   
   $xyz="{$x}:{$y}:{$z}";
   $block=$this->CBD->get("方块数据");
   
   if(in_array($xyz,$block)){
    $event->setCancelled(true);
    
    $event->getPlayer()->sendMessage("§a=====智能保护系统=====\n§e为了不影响游戏的平衡性, 生存模式下无法破坏创造模式下放置的方块!");
   }
  }
  else{
   //获取方块的坐标
   $x=$event->getBlock()->getX();
   $y=$event->getBlock()->getY();
   $z=$event->getBlock()->getZ();
   
   $xyz="{$x}:{$y}:{$z}";
   $block=$this->CBD->get("方块数据");
   
   if(in_array($xyz,$block)){
    
    $inv=array_search($xyz,$block);
    $inv=array_splice($block,$inv,1); 
    $this->CBD->set("方块数据",$block);
    $this->CBD->save();
   }
  }
 }
 
 //死亡掉落检测
 public function onDeathDrop(PlayerDeathEvent $event){
  $dropSwitch=$this->PFT->get("死亡掉落");
  if($dropSwitch=="关"){
   $event->setDrops(array(Item::get(0,0,0)));
  }
 }
 
 public function onDisable(){
 //插件卸载提示
  $this->getServer()->getLogger()->warning("§bPrivilegePlayerGo§6已安全卸载!");
 }
 
}