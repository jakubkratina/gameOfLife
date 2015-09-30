<?php

class Result
{
    const ISOLATED = 1,
        OVERCROWDED = 2,
        BIRTH = 3;

    static $message = [
        1 => 'isolated',
        2 => 'overcrowded',
        3 => 'birth'
    ];
}