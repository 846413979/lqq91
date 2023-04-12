<?php

namespace app\common\enum;

class Order
{
    const UnknowPayType = 0;
    const PayTypeLimit = 1;
    const PayTypeBalance = 2;
    const PayTypeWx = 3;
    const PayTypeAli = 4;
    const PayTypeTransfer = 5;
    const endPayType = 6;
}
