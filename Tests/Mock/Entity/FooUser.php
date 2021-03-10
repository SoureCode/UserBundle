<?php

namespace SoureCode\Bundle\User\Tests\Mock\Entity;

use Doctrine\ORM\Mapping as ORM;
use SoureCode\Component\User\Model\Advanced\AdvancedUser;

/**
 * @ORM\Entity(repositoryClass=UserRepositoy::class)
 * @ORM\Table(name="`user`")
 */
class FooUser extends AdvancedUser
{
}
