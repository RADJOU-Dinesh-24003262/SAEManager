<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Models\User\Professor;
use Models\User\Client;
use Models\User\User;

/**
 * Unit tests for Professor and Client models
 */
#[CoversClass(Professor::class)]
#[CoversClass(Client::class)]
#[CoversClass(User::class)]
class ProfessorClientModelTest extends TestCase
{
    // Professor tests
    #[Test]
    public function professorCanBeInstantiated(): void
    {
        $data = [
            'amu_id' => 'prof123',
            'first_name' => 'Marie',
            'last_name' => 'Curie',
            'email' => 'marie.curie@univ-amu.fr',
            'phone' => '0412345678'
        ];

        $professor = new Professor($data);

        $this->assertInstanceOf(Professor::class, $professor);
        $this->assertEquals('Marie', $professor->getFirstName());
        $this->assertEquals('Curie', $professor->getLastName());
    }

    #[Test]
    public function professorHasCorrectUserType(): void
    {
        $professor = new Professor(['first_name' => 'Test']);

        $this->assertEquals('professor', $professor->getUserType());
        $this->assertTrue($professor->isProfessor());
        $this->assertFalse($professor->isStudent());
        $this->assertFalse($professor->isClient());
    }

    #[Test]
    public function professorGettersWork(): void
    {
        $data = [
            'amu_id' => 'prof456',
            'first_name' => 'Albert',
            'last_name' => 'Einstein',
            'email' => 'albert.einstein@univ-amu.fr',
            'phone' => '0412345678'
        ];

        $professor = new Professor($data);

        $this->assertEquals('prof456', $professor->getAmuId());
        $this->assertEquals('albert.einstein@univ-amu.fr', $professor->getEmail());
        $this->assertEquals('0412345678', $professor->getPhone());
        $this->assertEquals('Albert Einstein', $professor->getFullName());
    }

    #[Test]
    public function professorPasswordCanBeSetAndHashed(): void
    {
        $professor = new Professor(['first_name' => 'Test']);
        $plainPassword = 'SecurePassword123';

        $professor->setPassword($plainPassword);
        $hashedPassword = $professor->getPasswordHash();

        $this->assertNotEmpty($hashedPassword);
        $this->assertNotEquals($plainPassword, $hashedPassword);
        $this->assertTrue(password_verify($plainPassword, $hashedPassword));
    }

    // Client tests
    #[Test]
    public function clientCanBeInstantiated(): void
    {
        $data = [
            'first_name' => 'Jean',
            'last_name' => 'Entreprise',
            'email' => 'jean@company.com',
            'phone' => '0612345678',
            'organisation' => 'TechCorp'
        ];

        $client = new Client($data);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals('Jean', $client->getFirstName());
        $this->assertEquals('Entreprise', $client->getLastName());
    }

    #[Test]
    public function clientHasCorrectUserType(): void
    {
        $client = new Client(['first_name' => 'Test']);

        $this->assertEquals('client', $client->getUserType());
        $this->assertTrue($client->isClient());
        $this->assertFalse($client->isStudent());
        $this->assertFalse($client->isProfessor());
    }

    #[Test]
    public function clientGettersWork(): void
    {
        $data = [
            'first_name' => 'Sophie',
            'last_name' => 'Martin',
            'email' => 'sophie@company.fr',
            'phone' => '0623456789',
            'organisation' => 'InnovateCorp'
        ];

        $client = new Client($data);

        $this->assertEquals('InnovateCorp', $client->getOrganisation());
        $this->assertEquals('sophie@company.fr', $client->getEmail());
        $this->assertEquals('0623456789', $client->getPhone());
        $this->assertEquals('Sophie Martin', $client->getFullName());
    }

    #[Test]
    public function clientPasswordCanBeSetAndHashed(): void
    {
        $client = new Client(['first_name' => 'Test']);
        $plainPassword = 'ClientSecurePass123';

        $client->setPassword($plainPassword);
        $hashedPassword = $client->getPasswordHash();

        $this->assertNotEmpty($hashedPassword);
        $this->assertNotEquals($plainPassword, $hashedPassword);
        $this->assertTrue(password_verify($plainPassword, $hashedPassword));
    }

    #[Test]
    public function clientWithEmptyOrganisationIsHandled(): void
    {
        $data = [
            'first_name' => 'Test',
            'organisation' => ''
        ];

        $client = new Client($data);

        $this->assertEquals('', $client->getOrganisation());
    }

    // Common User tests
    #[Test]
    public function allUserTypesIgnorePasswordFields(): void
    {
        $data = [
            'first_name' => 'Test',
            'password' => 'should_be_ignored',
            'passwordverif' => 'also_ignored',
            'terms' => 'ignored_too'
        ];

        $professor = new Professor($data);
        $client = new Client($data);

        $this->assertInstanceOf(Professor::class, $professor);
        $this->assertInstanceOf(Client::class, $client);
    }

    #[Test]
    public function differentPasswordHashesAreGenerated(): void
    {
        $password = 'SamePassword123';

        $professor = new Professor(['first_name' => 'Prof']);
        $client = new Client(['first_name' => 'Client']);

        $professor->setPassword($password);
        $client->setPassword($password);

        // Hashes should be different (bcrypt uses random salt)
        $this->assertNotEquals($professor->getPasswordHash(), $client->getPasswordHash());

        // But both should verify correctly
        $this->assertTrue(password_verify($password, $professor->getPasswordHash()));
        $this->assertTrue(password_verify($password, $client->getPasswordHash()));
    }

    #[Test]
    public function userTypesAreDistinct(): void
    {
        $professor = new Professor(['first_name' => 'Prof']);
        $client = new Client(['first_name' => 'Client']);

        $this->assertNotEquals($professor->getUserType(), $client->getUserType());
        $this->assertTrue($professor->isProfessor());
        $this->assertTrue($client->isClient());
        $this->assertFalse($professor->isClient());
        $this->assertFalse($client->isProfessor());
    }

    #[Test]
    public function emptyDataCreatesValidObjects(): void
    {
        $professor = new Professor([]);
        $client = new Client([]);

        $this->assertInstanceOf(Professor::class, $professor);
        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals('professor', $professor->getUserType());
        $this->assertEquals('client', $client->getUserType());
    }
}
