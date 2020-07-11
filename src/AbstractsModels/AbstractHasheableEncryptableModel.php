<?php

namespace OdoSha\Scytale\AbstractsModels;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use OdoSha\Scytale\Encryptable\Traits\Encryptable;
use OdoSha\Scytale\Encryptable\Traits\Hasheable;

abstract class AbstractHasheableEncryptableModel extends Model
{
  use Hasheable;
  protected $hasheable = [];
  abstract protected function setHasheableAttributes(array $hasheable): void;
}
