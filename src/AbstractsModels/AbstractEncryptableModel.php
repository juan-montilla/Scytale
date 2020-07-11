<?php

namespace OdoSha\Scytale\AbstractsModels;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use OdoSha\Scytale\Encryptable\Traits\Encryptable;
use OdoSha\Scytale\Encryptable\Traits\Hasheable;

abstract class AbstractEncryptableModel extends Model
{
  use Encryptable;
  protected $encryptable;
  abstract protected function setEncryptableAttributes(array $encryptable): void;
}
