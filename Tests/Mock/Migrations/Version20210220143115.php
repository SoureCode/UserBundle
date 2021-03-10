<?php

declare(strict_types=1);

namespace SoureCode\Bundle\User\Tests\Mock\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210220143115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(255) NOT NULL, canonical_email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, salt VARCHAR(255) DEFAULT NULL, roles TEXT NOT NULL, enabled BOOLEAN NOT NULL, last_login TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, verified_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, locked BOOLEAN NOT NULL, expires_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, credentials_expires_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649FD10FFAF ON "user" (canonical_email)');
        $this->addSql('COMMENT ON COLUMN "user".roles IS \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('DROP TABLE "user"');
    }
}
