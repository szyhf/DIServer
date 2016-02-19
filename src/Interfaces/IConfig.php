<?php

namespace DIServer\Interfaces;
//备注，因为重载机制问题，如果使用鸟哥的Yaconf作为配置，则普通的Reload是无法重载配置的。

interface IConfig extends IStorage
{

}