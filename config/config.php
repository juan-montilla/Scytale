<?php

/*
 * You can place your custom package configuration in here.
 */
return [
  'algo' => env('SCYTALE_HASH_ALGO', 'whirlpool'),
  'salt' => env('SCYTALE_SALT', 'abc123')
];