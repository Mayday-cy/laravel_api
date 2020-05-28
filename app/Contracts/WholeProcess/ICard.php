<?php


namespace App\Contracts\WholeProcess;


use App\Entitys\WholeProcess\CardModel;

interface ICard
{
    public function getCard(CardModel $cardModel);

    public function saveCard(CardModel $cardModel);

    public function checkFromHis(CardModel $cardModel);
}
