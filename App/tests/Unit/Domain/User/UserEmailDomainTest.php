<?php

namespace Tests\Unit\Domain\User;

use App\Domain\User\Client;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\User\User;
use App\Domain\User\Student;
use App\Domain\User\Professor;
use ReflectionMethod;

#[CoversClass(User::class)]
#[CoversClass(Student::class)]
#[CoversClass(Professor::class)]
#[CoversClass(Client::class)]
class UserEmailDomainTest extends TestCase
{
    #[Test]
    public function itAppendsStudentDomainWhenMissing(): void
    {
        $data = [
            'user_type' => 'student',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe', // No domain
            'phone' => '1234567890',
            'password' => 'secret',
            'amu_id' => '123',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TP1',
            'major' => 'Info'
        ];

        $user = User::createFromRegistrationData($data);

        $this->assertInstanceOf(Student::class, $user);
        $this->assertEquals('john.doe@etu.univ-amu.fr', $user->getEmail());
    }

    #[Test]
    public function itAppendsProfessorDomainWhenMissing(): void
    {
        $data = [
            'user_type' => 'professor',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith', // No domain
            'phone' => '0987654321',
            'password' => 'secret',
            'amu_id' => 'prof123'
        ];

        $user = User::createFromRegistrationData($data);

        $this->assertInstanceOf(Professor::class, $user);
        $this->assertEquals('jane.smith@univ-amu.fr', $user->getEmail());
    }

    #[Test]
    public function itDoesNotAppendDomainIfAlreadyPresent(): void
    {
        $data = [
            'user_type' => 'student',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@gmail.com', // External domain
            'phone' => '1234567890',
            'password' => 'secret',
            'amu_id' => '123',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TP1',
            'major' => 'Info'
        ];

        $user = User::createFromRegistrationData($data);

        $this->assertEquals('john.doe@gmail.com', $user->getEmail());
    }

    #[Test]
    public function itDoesNotAppendDomainForClient(): void
    {
        // Clients typically provide full email, but let's check behavior.
        // The addDomainNameToEmail logic checks isStudent() or isProfessor().
        // So Client should be untouched even if no domain (though invalid email usually).

        $data = [
            'user_type' => 'client',
            'first_name' => 'Corp',
            'last_name' => 'Inc',
            'email' => 'contact', // No domain
            'phone' => '1111111111',
            'password' => 'secret',
            'organisation' => 'Corp Inc'
        ];

        $user = User::createFromRegistrationData($data);

        // Based on User::addDomainNameToEmail implementation:
        // if (str_contains($this->email, '@')) return;
        // if ($this->isStudent()) ... elseif ($this->isProfessor()) ...
        // So client logic falls through and does nothing.

        $this->assertEquals('contact', $user->getEmail());
    }
}
