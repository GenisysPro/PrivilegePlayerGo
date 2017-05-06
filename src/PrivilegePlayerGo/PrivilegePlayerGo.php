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
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
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
  $this->getServer()->getLogger()->warning("§bPrivilegePlayerGo§6已成功运行在PHP版本为: §e".(PHP_VERSION)."§6的§e".(PHP_OS)."§6系统上.");
  
  //创建配置文件
  @mkdir($this->getDataFolder(),0777,true);
  @mkdir($this->getDataFolder()."InternalData/",0777,true);
  @mkdir($this->getDataFolder()."CDK_Data/",0777,true);
  @mkdir($this->getDataFolder()."InternalData/PlayerData/",0777,true);
  
  //特权玩家列表
  $this->A=new Config($this->getDataFolder()."/InternalData/PlayerData/PlayerList-1.yml",Config::YAML,array("玩家列表"=>array()));
  $this->B=new Config($this->getDataFolder()."/InternalData/PlayerData/PlayerList-2.yml",Config::YAML,array("玩家列表"=>array()));
  $this->C=new Config($this->getDataFolder()."/InternalData/PlayerData/PlayerList-3.yml",Config::YAML,array("玩家列表"=>array()));
  
  //特权时间数据
  $this->PPT=new Config($this->getDataFolder()."/InternalData/PrivilegeTime.yml",Config::YAML,array());
  
  //特权烟花次数
  $this->PFN=new Config($this->getDataFolder()."/InternalData/FireNumber.yml",Config::YAML,array());
  
  //玩家背包数据
  $this->PBD=new Config($this->getDataFolder()."/InternalData/BackpackData.yml",Config::YAML,array());
  
  //创造方块数据
  $this->CBD=new Config($this->getDataFolder()."/InternalData/BlockData.yml",Config::YAML,array("方块数据"=>array()));
  
  //玩家箱子数据
  $this->PCD=new Config($this->getDataFolder()."/InternalData/ChestData.yml",Config::YAML,array());
  
  //兑换码数据
  $this->CDK=new Config($this->getDataFolder()."/CDK_Data/CDK_Data.yml",Config::YAML,array("提示"=>"命令中请使用 名称 代替玩家名称"));
  
  //特权权限设置
  $this->PFT=new Config($this->getDataFolder()."特权配置.yml",Config::YAML,array(
  "功能设置"=>[
   "背包保存"=>"开",
   "死亡掉落"=>"关",
   "个人箱子"=>"开",
   "聊天美化"=>"开",
   "名称美化"=>"开"
  ],
  
  "聊天格式"=>" §b[称号]§f-§6名称§e",
  
  "头部名称"=>" §b称号§6-§e名称",
  
  "普通玩家"=>[
   "普通称号"=>"普通玩家",
  ],
  
  "普通特权"=>[
   "特权称号"=>"普通特权",
   "飞行模式"=>"开",
   "切换模式"=>"开",
   "特权传送"=>"开",
   "改变时间"=>"开",
   "燃放烟花"=>"开",
   "发射倍数"=>1,
  ],
   "高级特权"=>[
   "特权称号"=>"高级特权",
   "飞行模式"=>"开",
   "切换模式"=>"开",
   "特权传送"=>"开",
   "改变时间"=>"开",
   "燃放烟花"=>"开",
   "发射倍数"=>1,
  ],
  "顶级特权"=>[
   "特权称号"=>"顶级特权",
   "飞行模式"=>"开",
   "切换模式"=>"开",
   "特权传送"=>"开",
   "改变时间"=>"开",
   "燃放烟花"=>"开",
   "发射倍数"=>1,
  ]
  ));
 }
 
 public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
 
  //指令纠错
  if($cmd->getName()=="pp"){
   if(isset($args[0])){
    if($args[0]!="a" AND $args[0]!="b" AND $args[0]!="c" AND $args[0]!="remove" AND $args[0]!="list" AND $args[0]!="info" AND $args[0]!="fire" AND $args[0]!="gm" AND $args[0]!="fly" AND $args[0]!="help" AND $args[0]!="bag" AND $args[0]!="reload" AND $args[0]!="go" AND $args[0]!="cdk" AND $args[0]!="time"){
     $sender->sendMessage("§b=====特权玩家系统=====\n§6特权系统指令帮助: §e/pp help");
    }
   }
  }
 
  //添加顶级特权玩家
  if($cmd->getName()=="pp"){
  
   $tagSwitch=$this->PFT->get("功能设置")["聊天美化"];
   $tagsSwitch=$this->PFT->get("功能设置")["名称美化"];
  
   if($sender->isOp()){
    if(isset($args[0])){
     if($args[0]=="a"){
      if(isset($args[1])){
       if(isset($args[2])){
        if(is_numeric($args[2])){
         if($args[2]!=0){
          if($this->PPT->exists($args[1])){
           $sender->sendMessage("§b=====特权玩家系统=====\n§6特权玩家§e{$args[1]}§6的特权时间尚未过期, 无法再次添加已有的特权玩家!");
          }
          else{
           $day=intval($args[2]);//字符串数据化
            
           $fireNumber=$day*$this->PFT->get("顶级特权")["发射倍数"];
            
           $addTime=time();//添加时的时间戳
           $getTime=$day*86400;//特权时间的时间戳
           $allTime=$addTime+$getTime;//总的时间戳
            
           $PP=$this->A->get("玩家列表");
          
           if(!in_array($args[1],$PP)){
                                        $PP[]=$args[1];
                                  	  $this->A->set("玩家列表",$PP);
              						  $this->A->save();
           }
             
           $this->PPT->set($args[1],$allTime);
           $this->PPT->save();
            
           $this->PFN->set($args[1],$fireNumber);
           $this->PFN->save();
             
           //如果这个玩家在线
           if($this->getServer()->getPlayer($args[1])!== null){
            $player=$this->getServer()->getPlayer($args[1]);
            $playerName=$player->getName();
              
            $nameTag=$this->PFT->get("顶级特权")["特权称号"];
    
            //字符转义
            $target=array("称号","名称");
            $targets=array($nameTag,$playerName);
            $tag=str_replace($target,$targets,$this->PFT->get("聊天格式"));
            $tags=str_replace($target,$targets,$this->PFT->get("头部名称"));
    
            if($tagSwitch=="开"){
             $player->setDisplayName($tag);
            }
    
            if($tagsSwitch=="开"){
             $player->setNameTag($tags);
            }
           }
            
           $sender->sendMessage("§b=====特权玩家系统=====\n§6成功添加顶级特权玩家: §e{$args[1]}§6, 特权时间为: §e{$day}§6天.");
           $this->getServer()->broadcastMessage("§b=====特权玩家系统=====\n§6恭喜玩家§e{$args[1]}§6成为一名新的顶级特权玩家!");
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
        $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp a <玩家名称> <时间/天>");
       }
      }
      else{
       $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp a <玩家名称> <时间/天>");
      }
     }
     
     //添加高级特权玩家
     if($args[0]=="b"){
      if(isset($args[1])){
       if(isset($args[2])){
        if(is_numeric($args[2])){
         if($args[2]!=0){
          if($this->PPT->exists($args[1])){
           $sender->sendMessage("§b=====特权玩家系统=====\n§6特权玩家§e{$args[1]}§6的特权时间尚未过期, 无法再次添加已有的特权玩家!");
          }
          else{
           $day=intval($args[2]);//字符串数据化
            
           $fireNumber=$day*$this->PFT->get("高级特权")["发射倍数"];
          
           $addTime=time();//添加时的时间戳
           $getTime=$day*86400;//特权时间的时间戳
           $allTime=$addTime+$getTime;//总的时间戳
          
           $PP=$this->B->get("玩家列表");
            
           if(!in_array($args[1],$PP)){
            $PP[]=$args[1];
            $this->B->set("玩家列表",$PP);
            $this->B->save();
           }
            
           $this->PPT->set($args[1],$allTime);
           $this->PPT->save();
            
           $this->PFN->set($args[1],$fireNumber);
           $this->PFN->save();
             
           //如果这个玩家在线
           if($this->getServer()->getPlayer($args[1])!== null){
            $player=$this->getServer()->getPlayer($args[1]);
            $playerName=$player->getName();
              
            $nameTag=$this->PFT->get("高级特权")["特权称号"];
  
            //字符转义
            $target=array("称号","名称");
            $targets=array($nameTag,$playerName);
            $tag=str_replace($target,$targets,$this->PFT->get("聊天格式"));
            $tags=str_replace($target,$targets,$this->PFT->get("头部名称"));
    
            if($tagSwitch=="开"){
             $player->setDisplayName($tag);
            }
    
            if($tagsSwitch=="开"){
             $player->setNameTag($tags);
            }
           }
            
           $sender->sendMessage("§b=====特权玩家系统=====\n§6成功添加高级特权玩家: §e{$args[1]}§6, 特权时间为: §e{$day}§6天.");
           $this->getServer()->broadcastMessage("§b=====特权玩家系统=====\n§6恭喜玩家§e{$args[1]}§6成为一名新的高级特权玩家!");
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
        $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp a <玩家名称> <时间/天>");
       }
      }
      else{
       $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp b <玩家名称> <时间/天>");
      }
     }
      
     //添加普通特权玩家
     if($args[0]=="c"){
      if(isset($args[1])){
       if(isset($args[2])){
        if(is_numeric($args[2])){
         if($args[2]!=0){
          if($this->PPT->exists($args[1])){
           $sender->sendMessage("§b=====特权玩家系统=====\n§6特权玩家§e{$args[1]}§6的特权时间尚未过期, 无法再次添加已有的特权玩家!");
          }
          else{
           $day=intval($args[2]);//字符串数据化
            
           $fireNumber=$day*$this->PFT->get("普通特权")["发射倍数"];
            
           $addTime=time();//添加时的时间戳
           $getTime=$day*86400;//特权时间的时间戳
           $allTime=$addTime+$getTime;//总的时间戳
            
           $PP=$this->C->get("玩家列表");
          
           if(!in_array($args[1],$PP)){
                                  	  $PP[]=$args[1];
                          			  $this->C->set("玩家列表",$PP);
                              		  $this->C->save();
           }
             
           $this->PPT->set($args[1],$allTime);
           $this->PPT->save();
            
           $this->PFN->set($args[1],$fireNumber);
           $this->PFN->save();
             
           //如果这个玩家在线
           if($this->getServer()->getPlayer($args[1])!== null){
            $player=$this->getServer()->getPlayer($args[1]);
            $playerName=$player->getName();
              
            $nameTag=$this->PFT->get("普通特权")["特权称号"];
    
            //字符转义
            $target=array("称号","名称");
            $targets=array($nameTag,$playerName);
            $tag=str_replace($target,$targets,$this->PFT->get("聊天格式"));
            $tags=str_replace($target,$targets,$this->PFT->get("头部名称"));
    
            if($tagSwitch=="开"){
             $player->setDisplayName($tag);
            }
    
            if($tagsSwitch=="开"){
             $player->setNameTag($tags);
            }
           }
            
           $sender->sendMessage("§b=====特权玩家系统=====\n§6成功添加普通特权玩家: §e{$args[1]}§6, 特权时间为: §e{$day}§6天.");
           $this->getServer()->broadcastMessage("§b=====特权玩家系统=====\n§6恭喜玩家§e{$args[1]}§6成为一名新的普通特权玩家!");
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
        $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp a <玩家名称> <时间/天>");
       }
      }
      else{
       $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp c <玩家名称> <时间/天>");
      }
     }
     
    }
    else{
     $sender->sendMessage("§b==cc===特权玩家系统=====\n§6特权系统指令帮助: §e/pp help");
    }
   }
  }
  
 
  if($cmd->getName()=="pp"){
   if(isset($args[0])){
    if($sender->isOp()){
       
     $senderName=$sender->getName();
     $fireNumber=$this->PFN->get($senderName);
       
     switch($args[0]){
      case"help":
       $sender->sendMessage("§b=====特权玩家系统=====\n§6特权系统指令帮助: §e/pp help\n§6添加一名顶级特权: §e/pp a <玩家名称> <时间/天>\n§6添加一名高级特权: §e/pp b <玩家名称> <时间/天>\n§6添加一名普通特权: §e/pp c <玩家名称> <时间/天>\n§6移除一名特权玩家: §e/pp remove <玩家名称>\n§6重新加载配置文件: §e/pp reload\n§6查看特权玩家列表: §e/pp list\n§6切换我的游戏模式: §e/pp gm\n§6开启关闭飞行模式: §e/pp fly\n§6查看我的个人信息: §e/pp info\n§6释放特权玩家烟花: §e/pp fire\n§6传送到某玩家身边: §e/pp go <玩家名称>\n§6改变当前世界时间: §e/pp time <数字>");
      break;
       
      case"list":
       $Alist=$this->A->get("玩家列表");
       $A=implode(", ",$Alist);
         
       $Blist=$this->B->get("玩家列表");
       $B=implode(", ",$Blist);
         
       $Clist=$this->C->get("玩家列表");
       $C=implode(", ",$Clist);
       $sender->sendMessage("§b=====特权玩家系统=====\n§6顶级特权玩家列表: §e{$A}\n§6高级特权玩家列表: §e{$B}\n§6普通特权玩家列表: §e{$C}");
      break;
        
      case"reload":
       $this->PFT->reload();
       $this->CDK->reload();
       $sender->sendMessage("§b=====特权玩家系统=====\n§6配置文件重载完成!");
      break;
      
      case"remove":
       if(isset($args[1])){
        if($this->PPT->exists($args[1])){
       
         $this->PPT->remove($args[1]);
         $this->PPT->save();
        
         $this->PFN->remove($args[1]);
         $this->PFN->save();
        
         $a=$this->A->get("玩家列表");
         $b=$this->B->get("玩家列表");
         $c=$this->C->get("玩家列表");
        
         $tagSwitch=$this->PFT->get("功能设置")["聊天美化"];
         $tagsSwitch=$this->PFT->get("功能设置")["名称美化"];
        
         if(in_array($args[1],$a)){
          $PP=$this->A->get("玩家列表");
          $inv=array_search($args[1],$PP);
          $inv=array_splice($PP,$inv,1); 
          $this->A->set("玩家列表",$PP);
          $this->A->save();
         }
         if(in_array($args[1],$b)){
          $PP=$this->B->get("玩家列表");
          $inv=array_search($args[1],$PP);
          $inv=array_splice($PP,$inv,1); 
          $this->B->set("玩家列表",$PP);
          $this->B->save();
         }
         if(in_array($args[1],$c)){
          $PP=$this->C->get("玩家列表");
          $inv=array_search($args[1],$PP);
          $inv=array_splice($PP,$inv,1); 
          $this->C->set("玩家列表",$PP);
          $this->C->save();
         }
        
         //如果这个玩家在线
         if($this->getServer()->getPlayer($args[1])!== null){
          $player=$this->getServer()->getPlayer($args[1]);
          $playerName=$player->getName();
              
          $nameTag=$this->PFT->get("普通玩家")["普通称号"];
    
          //字符转义
          $target=array("称号","名称");
          $targets=array($nameTag,$playerName);
          $tag=str_replace($target,$targets,$this->PFT->get("聊天格式"));
          $tags=str_replace($target,$targets,$this->PFT->get("头部名称"));
    
          if($tagSwitch=="开"){
           $player->setDisplayName($tag);
          }
    
          if($tagsSwitch=="开"){
           $player->setNameTag($tags);
          }
         }
        
         $sender->sendMessage("§b=====特权玩家系统=====\n§6成功移除特权玩家: §e{$args[1]}");
        }
        else{
         $sender->sendMessage("§b=====特权玩家系统=====\n§e{$args[1]}§6不是特权玩家, 无法移除!");
        }
       }
       else{
        $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp remove <玩家名称>");
       }
      break;
      
     }
    }
    
    //如果发送者是特权玩家
    $senderName=$sender->getName();
    if($this->PPT->exists($senderName)){
     switch($args[0]){
      case"help":
       if(!$sender->isOp()){
        $sender->sendMessage("§b=====特权玩家系统=====\n§6特权系统指令帮助: §e/pp help\n§6查看特权玩家列表: §e/pp list\n§6切换我的游戏模式: §e/pp gm\n§6开启关闭飞行模式: §e/pp fly\n§6查看我的个人信息: §e/pp info\n§6释放特权玩家烟花: §e/pp fire\n§6传送到某玩家身边: §e/pp go <玩家名称>\n§6改变当前世界时间: §e/pp time <数字>");
       }
      break;
        
      case"list":
       if(!$sender->isOp()){
        $Alist=$this->A->get("玩家列表");
        $A=implode(", ",$Alist);
         
        $Blist=$this->B->get("玩家列表");
        $B=implode(", ",$Blist);
         
        $Clist=$this->C->get("玩家列表");
        $C=implode(", ",$Clist);
        $sender->sendMessage("§b=====特权玩家系统=====\n§6顶级特权玩家列表: §e{$A}\n§6高级特权玩家列表: §e{$B}\n§6普通特权玩家列表: §e{$C}");
       }
      break;
      
     }
       
     $fireNumber=$this->PFN->get($senderName);
       
     $a=$this->A->get("玩家列表");
     $b=$this->B->get("玩家列表");
     $c=$this->C->get("玩家列表");
        
     //如果是顶级特权玩家
     if(in_array($senderName,$a)){
        
      $GMSwitch=$this->PFT->get("顶级特权")["切换模式"];
      $FlySwitch=$this->PFT->get("顶级特权")["飞行模式"];
      $FireSwitch=$this->PFT->get("顶级特权")["燃放烟花"];
      $TPSwitch=$this->PFT->get("顶级特权")["特权传送"];
      $TimeSwitch=$this->PFT->get("顶级特权")["改变时间"];
        
      switch($args[0]){
       case"info":
        $senderName=$sender->getName();
        $nowTime=time();//获取当前的时间戳
        $allTime=$this->PPT->get($senderName);//获取存储于配置文件中的总时间戳
        $time=($allTime-$nowTime)/86400;
        $haveTime=ceil($time);//取整
        $sender->sendMessage("§b=====特权玩家系统=====\n§6我的特权玩家身份: §e顶级特权\n§6我的特权时间还剩: §e{$haveTime}天.\n§6我的特权烟花还剩: §e{$fireNumber}枚.");
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
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有切换模式权限");
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
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有飞行模式权限");
        }
       break;
          
       case"go":
        if(isset($args[1])){
         if($TPSwitch=="开"){
          //如果这个玩家在线
          if($this->getServer()->getPlayer($args[1])!== null){
           $player=$this->getServer()->getPlayer($args[1]);
            
           $sender->teleport($this->getServer()->getPlayer($args[1])->getPosition());
           $sender->sendMessage("§b=====特权玩家系统=====\n§6成功传送到玩家§e{$args[1]}§6的身旁.");
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6无法找到§e{$args[1]}§6玩家!");
          }
         }
         else{
          $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有传送权限");
         }
        }
        else{
         $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp go <玩家名称>");
        }
       break;
       
       case"time":
        if($TimeSwitch=="开"){
         if(isset($args[1])){
          if(is_numeric($args[1])){
           if($sender->isOp()){
            $this->getServer()->dispatchCommand($sender,"time set $args[1]");
           }
           else{
            $this->getServer()->addOp($senderName);
            $this->getServer()->dispatchCommand($sender,"time set $args[1]");
            $this->getServer()->removeOp($senderName);
           }
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp time <数字>");
          }
         }
         else{
          $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp time <数字>");
         }
        }
        else{
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有改变时间权限");
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
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有燃放烟花权限");
        }
       break;
      }
     }
        
     //如果是高级特权玩家
     if(in_array($senderName,$b)){
        
      $GMSwitch=$this->PFT->get("高级特权")["切换模式"];
      $FlySwitch=$this->PFT->get("高级特权")["飞行模式"];
      $FireSwitch=$this->PFT->get("高级特权")["燃放烟花"];
      $TPSwitch=$this->PFT->get("高级特权")["特权传送"];
      $TimeSwitch=$this->PFT->get("高级特权")["改变时间"];
         
      switch($args[0]){
       case"info":
        $senderName=$sender->getName();
        $nowTime=time();//获取当前的时间戳
        $allTime=$this->PPT->get($senderName);//获取存储于配置文件中的总时间戳
        $time=($allTime-$nowTime)/86400;
        $haveTime=ceil($time);//取整
        $sender->sendMessage("§b=====特权玩家系统=====\n§6我的特权玩家身份: §e高级特权\n§6我的特权时间还剩: §e{$haveTime}天.\n§6我的特权烟花还剩: §e{$fireNumber}枚.");
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
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有切换模式权限");
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
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有飞行模式权限");
        }
       break;
          
       case"go":
        if(isset($args[1])){
         if($TPSwitch=="开"){
          //如果这个玩家在线
          if($this->getServer()->getPlayer($args[1])!== null){
           $player=$this->getServer()->getPlayer($args[1]);
            
           $sender->teleport($this->getServer()->getPlayer($args[1])->getPosition());
           $sender->sendMessage("§b=====特权玩家系统=====\n§6成功传送到玩家§e{$args[1]}§6的身旁.");
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6无法找到§e{$args[1]}§6玩家!");
          }
         }
         else{
          $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有传送权限");
         }
        }
        else{
         $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp go <玩家名称>");
        }
       break;
       
       case"time":
        if($TimeSwitch=="开"){
         if(isset($args[1])){
          if(is_numeric($args[1])){
           if($sender->isOp()){
            $this->getServer()->dispatchCommand($sender,"time set $args[1]");
           }
           else{
            $this->getServer()->addOp($senderName);
            $this->getServer()->dispatchCommand($sender,"time set $args[1]");
            $this->getServer()->removeOp($senderName);
           }
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp time <数字>");
          }
         }
         else{
          $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp time <数字>");
         }
        }
        else{
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有改变时间权限");
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
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有燃放烟花权限");
        }
       break;
      }
     }
        
     //如果是普通特权玩家
     if(in_array($senderName,$c)){
      $GMSwitch=$this->PFT->get("普通特权")["切换模式"];
      $FlySwitch=$this->PFT->get("普通特权")["飞行模式"];
      $FireSwitch=$this->PFT->get("普通特权")["燃放烟花"];
      $TPSwitch=$this->PFT->get("普通特权")["特权传送"];
      $TimeSwitch=$this->PFT->get("普通特权")["改变时间"];
         
      switch($args[0]){
       case"info":
        $senderName=$sender->getName();
        $nowTime=time();//获取当前的时间戳
        $allTime=$this->PPT->get($senderName);//获取存储于配置文件中的总时间戳
        $time=($allTime-$nowTime)/86400;
        $haveTime=ceil($time);//取整
        $sender->sendMessage("§b=====特权玩家系统=====\n§6我的特权玩家身份: §e普通特权\n§6我的特权时间还剩: §e{$haveTime}天.\n§6我的特权烟花还剩: §e{$fireNumber}枚.");
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
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有切换模式权限");
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
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有飞行模式权限");
        }
       break;
          
       case"go":
        if(isset($args[1])){
         if($TPSwitch=="开"){
          //如果这个玩家在线
          if($this->getServer()->getPlayer($args[1])!== null){
           $player=$this->getServer()->getPlayer($args[1]);
            
           $sender->teleport($this->getServer()->getPlayer($args[1])->getPosition());
           $sender->sendMessage("§b=====特权玩家系统=====\n§6成功传送到玩家§e{$args[1]}§6的身旁.");
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6无法找到§e{$args[1]}§6玩家!");
          }
         }
         else{
          $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有传送权限");
         }
        }
        else{
         $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp go <玩家名称>");
        }
       break;
       
       case"time":
        if($TimeSwitch=="开"){
         if(isset($args[1])){
          if(is_numeric($args[1])){
           if($sender->isOp()){
            $this->getServer()->dispatchCommand($sender,"time set $args[1]");
           }
           else{
            $this->getServer()->addOp($senderName);
            $this->getServer()->dispatchCommand($sender,"time set $args[1]");
            $this->getServer()->removeOp($senderName);
           }
          }
          else{
           $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp time <数字>");
          }
         }
         else{
          $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp time <数字>");
         }
        }
        else{
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有改变时间权限");
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
         $sender->sendMessage("§b=====特权玩家系统=====\n§6你还未拥有燃放烟花权限");
        }
       break;
      }
     }
    }
   }
  }
  
  //背包数据返回
  if($cmd->getName()=="pp"){
   if(isset($args[0])){
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
       }
      }
      else{
       $sender->sendMessage("§a=====智能保护系统=====\n§e无法找到你的背包数据!");
      }
     }
    }
    //CDK兑换与创建
    if($args[0]=="cdk"){
     if(isset($args[1])){
      if($this->CDK->exists($args[1])){
       if($sender instanceof Player){
       
        $senderName=$sender->getName();
        $nowGM=$sender->getGamemode();
        
        if($nowGM==0){
        
         $cdk=str_replace("名称",$senderName,$this->CDK->get($args[1]));
        
         if($sender->isOp()){
        
          for($i=0;$i<count($cdk);$i++){
           $cdks=$cdk[$i];
           $this->getServer()->dispatchCommand($sender,$cdks);
          }
         }
         else{
        
          $this->getServer()->addOp($senderName);
       
          for($i=0;$i<count($cdk);$i++){
           $cdks=$cdk[$i];
           $this->getServer()->dispatchCommand($sender,$cdks);
          }
        
          $this->getServer()->removeOp($senderName);
         }
          
         $this->CDK->remove($args[1]);
         $this->CDK->save();
         
         $this->getServer()->broadcastMessage("§b=====特权玩家系统=====\n§6恭喜玩家§e{$senderName}§6成功兑换了CDK!");
        }
        else{
         $sender->sendMessage("§b=====特权玩家系统=====\n§6请在生存模式下兑换CDK!");
        }
       }
       else{
        $sender->sendMessage("§b=====特权玩家系统=====\n§6请在游戏中兑换CDK!");
       }
      }
      else{
       if($sender->isOp()){
        $this->CDK->set($args[1],["give 名称 2:0 64","give 名称 3:0 64"]);
        $this->CDK->save();
        $sender->sendMessage("§b=====特权玩家系统=====\n§6创建成功, 兑换码为: §e{$args[1]}§6, 请到路径为§eplugins/PrivilegePlayerGo!/CDK_Data/CDK_Data.yml§6文件内相应的兑换码下添加指令, 然后输入指令§e/pp reload§6重载配置文件即可!");
       }
       else{
        $sender->sendMessage("§b=====特权玩家系统=====\n§6兑换失败, 请输入正确的CDK兑换码!");
       }
      }
     }
     else{
      $sender->sendMessage("§b=====特权玩家系统=====\n§6正确用法: §e/pp cdk <兑换码>");
     }
    }
   }
  }
  
 }
 
 public function onPlayerJoin(PlayerJoinEvent $event){
  $player=$event->getPlayer();
  $playerName=$player->getName();
  
  $a=$this->A->get("玩家列表");
  $b=$this->B->get("玩家列表");
  $c=$this->C->get("玩家列表");
  
  $tagSwitch=$this->PFT->get("功能设置")["聊天美化"];
  $tagsSwitch=$this->PFT->get("功能设置")["名称美化"];
 
  if($this->PPT->exists($playerName)){
    
   $allTime=$this->PPT->get($playerName);
   $nowTime=time();
     
   if($allTime>=$nowTime){
    sleep(1);
    $this->getServer()->broadcastMessage("§b=====特权玩家系统=====\n§6欢迎特权玩家§e{$playerName}§6加入服务器!§f");
    $player->getPlayer()->sendMessage("§6亲爱的特权玩家§e{$playerName}§6, 欢迎回到服务器!");
   }
   if($allTime<$nowTime){
    $player->getPlayer()->sendMessage("§b=====特权玩家系统=====\n§6你的特权玩家身份已过期!");
     
    $this->PPT->remove($playerName);
    $this->PPT->save();
      
    $this->PFN->remove($playerName);
    $this->PFN->save();
    
    //如果是顶级特权玩家
    if(in_array($playerName,$a)){
     $PP=$this->A->get("玩家列表");
     $inv=array_search($playerName,$PP);
     $inv=array_splice($PP,$inv,1); 
     $this->A->set("玩家列表",$PP);
     $this->A->save();
    }
    
    //如果是高级特权玩家
    if(in_array($playerName,$b)){
     $PP=$this->B->get("玩家列表");
     $inv=array_search($playerName,$PP);
     $inv=array_splice($PP,$inv,1); 
     $this->B->set("玩家列表",$PP);
     $this->B->save();
    }
    
    //如果是普通特权玩家
    if(in_array($playerName,$c)){
     $PP=$this->C->get("玩家列表");
     $inv=array_search($playerName,$PP);
     $inv=array_splice($PP,$inv,1); 
     $this->C->set("玩家列表",$PP);
     $this->C->save();
    }
      
    $nowGM=$player->getGamemode();
      
    if($nowGM==1 AND $nowGM!=0 AND $nowGM!=2 AND $nowGM!=3){
     $player->setGamemode(0);
    }
    if($player->getAllowFlight()){
     $player->setAllowFlight(false);
    }
   }
   
   //如果是顶级特权玩家
   if(in_array($playerName,$a)){
    $nameTag=$this->PFT->get("顶级特权")["特权称号"];
    
    //字符转义
    $target=array("称号","名称");
    $targets=array($nameTag,$playerName);
    $tag=str_replace($target,$targets,$this->PFT->get("聊天格式"));
    $tags=str_replace($target,$targets,$this->PFT->get("头部名称"));
    
    if($tagSwitch=="开"){
     $player->setDisplayName($tag);
    }
    
    if($tagsSwitch=="开"){
     $player->setNameTag($tags);
    }
   }
   
   //如果是高级特权玩家
   if(in_array($playerName,$b)){
    $nameTag=$this->PFT->get("高级特权")["特权称号"];
    
    //字符转义
    $target=array("称号","名称");
    $targets=array($nameTag,$playerName);
    $tag=str_replace($target,$targets,$this->PFT->get("聊天格式"));
    $tags=str_replace($target,$targets,$this->PFT->get("头部名称"));
    
    if($tagSwitch=="开"){
     $player->setDisplayName($tag);
    }
    
    if($tagsSwitch=="开"){
     $player->setNameTag($tags);
    }
   }
   
   //如果是普通特权玩家
   if(in_array($playerName,$c)){
    $nameTag=$this->PFT->get("普通特权")["特权称号"];
    
    //字符转义
    $target=array("称号","名称");
    $targets=array($nameTag,$playerName);
    $tag=str_replace($target,$targets,$this->PFT->get("聊天格式"));
    $tags=str_replace($target,$targets,$this->PFT->get("头部名称"));
    
    if($tagSwitch=="开"){
     $player->setDisplayName($tag);
    }
    
    if($tagsSwitch=="开"){
     $player->setNameTag($tags);
    }
   }
   
  }
  //如果不是特权玩家
  else{
   $nowGM=$player->getGamemode();
      
   if($nowGM==1 AND $nowGM!=0 AND $nowGM!=2 AND $nowGM!=3){
    $player->setGamemode(0);
   }
   if($player->getAllowFlight()){
    $player->setAllowFlight(false);
   }
   
   //如果是普通玩家
   $nameTag=$this->PFT->get("普通玩家")["普通称号"];
    
   //字符转义
   $target=array("称号","名称");
   $targets=array($nameTag,$playerName);
   $tag=str_replace($target,$targets,$this->PFT->get("聊天格式"));
   $tags=str_replace($target,$targets,$this->PFT->get("头部名称"));
    
   if($tagSwitch=="开"){
     $player->setDisplayName($tag);
    }
    
   if($tagsSwitch=="开"){
    $player->setNameTag($tags);
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
   else{
    $chestSwitch=$this->PFT->get("功能设置")["个人箱子"];
    if($chestSwitch=="开"){
     $x=$event->getBlock()->getX();
     $y=$event->getBlock()->getY();
     $z=$event->getBlock()->getZ();
   
     $xyz="{$x}:{$y}:{$z}";
    
     if($this->PCD->exists($xyz)){
      $host=$this->PCD->get($xyz);
     
      //判断是不是箱子的主人
      if($playerName!=$host){
       $event->setCancelled(true);
       $player->sendMessage("§a=====智能保护系统=====\n§e你不是这个箱子的主人!");
      }
     }
    }
   }
     }
 }
 
 public function onModeChange(PlayerGameModeChangeEvent $event){
 
  $type=0;
  
  $BagSwitch=$this->PFT->get("功能设置")["背包保存"];
  
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
 
 //放置方块记录
 public function onPlaceBlock(BlockPlaceEvent $event){
  $mode=$event->getPlayer()->getGamemode();
  $id=intval($event->getBlock()->getId());
  $player=$event->getPlayer();
  $playerName=$player->getName();
  
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
  //个人箱子储存
  if($id==54){
   $chestSwitch=$this->PFT->get("功能设置")["个人箱子"];
   if($chestSwitch=="开"){
    //获取方块的坐标
    $x=$event->getBlock()->getX();
    $y=$event->getBlock()->getY();
    $z=$event->getBlock()->getZ();
   
    $xyz="{$x}:{$y}:{$z}";
   
    $this->PCD->set($xyz,$playerName);
    $this->PCD->save();
   
    $player->sendMessage("§a=====智能保护系统=====\n§e小贴士: 大型箱子是比小型箱子更加安全的个人箱子.");
   }
  }
  
  if($id==154){
   $event->setCancelled(true);
   $player->sendMessage("§a=====智能保护系统=====\n§e为了不影响游戏的平衡性, 服务器已禁止使用漏斗.");
  }
 }
 
 public function onItemHeld(PlayerItemHeldEvent $event){
  $id=intval($event->getItem()->getId());
  $player=$event->getPlayer();
  if($id==410){
   $event->setCancelled(true);
   $player->sendMessage("§a=====智能保护系统=====\n§e为了不影响游戏的平衡性, 服务器已禁止使用漏斗.");
  }
 }
 
 //破坏方块检测
 public function onBreakBlock(BlockBreakEvent $event){
  $player=$event->getPlayer();
  $mode=$player->getGamemode();
  $playerName=$player->getName();
  
  $id=intval($event->getBlock()->getId());
  
  //获取方块的坐标
  $x=$event->getBlock()->getX();
  $y=$event->getBlock()->getY();
  $z=$event->getBlock()->getZ();
   
  $xyz="{$x}:{$y}:{$z}";
  
  $block=$this->CBD->get("方块数据");
  
  if($id==54){
   $chestSwitch=$this->PFT->get("功能设置")["个人箱子"];
   if($chestSwitch=="开"){
    if($this->PCD->exists($xyz)){
     $host=$this->PCD->get($xyz);
     
     //判断是不是箱子的主人
     if($playerName==$host){
      $this->PCD->remove($xyz);
      $this->PCD->save();
     
      $player->sendMessage("§a=====智能保护系统=====\n§e成功销毁了你的个人箱子.");
     }
    }
   }
  }
  
  if(!$mode==1){
   if(in_array($xyz,$block)){
    $event->setCancelled(true);
    
    $event->getPlayer()->sendMessage("§a=====智能保护系统=====\n§e为了不影响游戏的平衡性, 生存模式下无法破坏创造模式下放置的方块!");
   }
  }
  else{
   
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
  $dropSwitch=$this->PFT->get("功能设置")["死亡掉落"];
  if($dropSwitch=="关"){
   $event->setDrops(array(Item::get(0,0,0)));
  }
 }
 
 public function onDisable(){
  //插件卸载提示
  $this->getServer()->getLogger()->warning("§bPrivilegePlayerGo§6已安全卸载!");
 }
 
}
