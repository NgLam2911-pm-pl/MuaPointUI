<?php

namespace LamPocketVN\MuaPoint;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{Command, CommandSender};
use pocketmine\Player;
use pocketmine\utils\Config;

use jojoe77777\FormAPI\CustomForm;

use onebone\economyapi\EconomyAPI;
use onebone\pointapi\PointAPI;

/**
 * Class MuaPoint
 * @package LamPocketVN\MuaPoint
 */
class MuaPoint extends PluginBase
{
    /**
     * @var $config
     */
    public $config;

    /**
     * @return mixed
     */
    public function getSetting()
    {
        return $this->cfg;
    }

    public function onEnable()
    {
        $this->saveResource("setting.yml");
        $this->config = new Config($this->getDataFolder() . "setting.yml", Config::YAML);
        $this->cfg = $this->config->getAll();
    }
    public function onDisable()
    {}

    /**
     * @param CommandSender $sender
     * @param Command $cmd
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        switch (strtolower($cmd->getName()))
        {
            case "muapoint":
                if (!$sender->hasPermission("muapoint.command"))
                {
                    return true;
                }
                $this->buyForm($sender);
                return true;
                break;
        }
        return true;
    }

    /**
     * @param Player $player
     */
    public function buyForm (Player $player)
    {
        $form = new CustomForm(function (Player $player, $data)
        {
            if (!isset($data[0]))
            {
                $player->sendMessage($this->getSetting()['msg']['null-input']);
                return;
            }
            if (!is_numeric($data[0]))
            {
                $player->sendMessage($this->getSetting()['msg']['not-numeric-input']);
                return;
            }
            $price = $data[0] * $this->getSetting()['price'];
            $money = EconomyAPI::getInstance()->myMoney($player);
            if ($money >= $price)
            {
                EconomyAPI::getInstance()->reduceMoney($player, $price);
                PointAPI::getInstance()->addPoint($player, $data[0]);
                $msg = str_replace("{point}", $data[0], $this->getSetting()['msg']['buy-done']);
                $player->sendMessage($msg);
            }
            else
            {
                $msg = str_replace("{price}", $price-$money, $this->getSetting()['msg']['buy-fail']);
                $player->sendMessage($msg);
            }
        });
        $form->setTitle($this->getSetting()['form']['title']);
        $form->addInput($this->getSetting()['form']['input'], "123456789");
        $form->sendToPlayer($player);
    }
}