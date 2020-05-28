<?php


namespace App\Contracts\Area;

/**
 * 创建人：Rex.栗田庆
 * 创建时间：2019-05-10 17:17
 * Interface AreaListInterface
 * @package App\Interfaces
 */
interface AreaListInterface
{
    //通过$id 获取 Area 模型
    public function getArea($id);

    //通过 $pid 获取 Area 子项
    public function getSubArea($pid);

    //通过 $id 获取父级
    public function getAreaChain($id);
}
