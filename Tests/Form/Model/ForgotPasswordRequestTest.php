<?php

namespace SoureCode\Bundle\User\Tests\Form\Model;

use PHPUnit\Framework\TestCase;
use SoureCode\Bundle\User\Form\Model\ForgotPasswordRequest;

class ForgotPasswordRequestTest extends TestCase
{
    public function testGetSetEmail(): void
    {
        // Arrange
        $model = new ForgotPasswordRequest();

        // Act and Assert
        self::assertNull($model->getEmail());
        $model->setEmail('foo');
        self::assertSame('foo', $model->getEmail());
    }
}
