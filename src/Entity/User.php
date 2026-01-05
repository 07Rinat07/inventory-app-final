<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User — базовая security-сущность.
 *
 * ВАЖНО:
 * - Только данные пользователя, без бизнес-логики.
 * - Все решения по доступу — через Voter/Policy.
 * - Preferences храним реляционно (без JSON).
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uniq_users_email', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, nullable: false)]
    private string $email;

    /**
     * @var list<string>
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * Хеш пароля (bcrypt/argon — управляется Symfony).
     */
    #[ORM\Column]
    private string $password;

    /**
     * Пользовательская тема интерфейса.
     * light | dark
     */
    #[ORM\Column(length: 10, options: ['default' => 'light'])]
    private string $theme = 'light';

    /**
     * Язык интерфейса.
     * en | ru
     */
    #[ORM\Column(length: 10, options: ['default' => 'en'])]
    private string $locale = 'en';

    /**
     * Инвентари пользователя (inverse-side для Inventory::$owner).
     *
     * Важно:
     * - owning side находится в Inventory::$owner (ManyToOne)
     * - это поле нужно Doctrine, потому что в Inventory стоит inversedBy="inventories"
     *
     * @var Collection<int, Inventory>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Inventory::class)]
    private Collection $inventories;

    /**
     * Создает новый экземпляр пользователя.
     */
    public function __construct()
    {
        // Инициализация коллекций — обязательна, иначе будут null/ошибки при add/remove
        $this->inventories = new ArrayCollection();
    }

    // -------------------------
    // Identifiers
    // -------------------------

    /**
     * Идентификатор пользователя.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Возвращает email пользователя.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Устанавливает email пользователя.
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Уникальный идентификатор пользователя (Security).
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // -------------------------
    // Roles
    // -------------------------

    /**
     * Возвращает роли пользователя.
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // Гарантируем базовую роль
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * Устанавливает роли пользователя.
     * @param list<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    // -------------------------
    // Password
    // -------------------------

    /**
     * Возвращает хешированный пароль.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Ставим хешированный пароль.
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    // -------------------------
    // Настройки интерфейса
    // -------------------------

    /**
     * Какая тема сейчас выбрана.
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * Меняем тему (пока только light или dark).
     */
    public function setTheme(string $theme): self
    {
        $this->theme = \in_array($theme, ['light', 'dark'], true)
            ? $theme
            : 'light';

        return $this;
    }

    /**
     * Язык пользователя.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Меняем язык (ru или en).
     */
    public function setLocale(string $locale): self
    {
        $this->locale = \in_array($locale, ['en', 'ru'], true)
            ? $locale
            : 'en';

        return $this;
    }

    // -------------------------
    // Инвентари (обратная сторона)
    // -------------------------

    /**
     * Список инвентарей, которыми владеет юзер.
     * @return Collection<int, Inventory>
     */
    public function getInventories(): Collection
    {
        return $this->inventories;
    }

    /**
     * Добавляем новый инвентарь юзеру.
     * Не забываем про двустороннюю связь для Doctrine.
     */
    public function addInventory(Inventory $inventory): self
    {
        if (!$this->inventories->contains($inventory)) {
            $this->inventories->add($inventory);
            $inventory->setOwner($this);
        }

        return $this;
    }

    /**
     * Убираем инвентарь из списка.
     */
    public function removeInventory(Inventory $inventory): self
    {
        // В БД owner не может быть NULL, так что просто убрать из коллекции мало,
        // нужно либо удалить сам инвентарь, либо сменить владельца в сервисе.
        $this->inventories->removeElement($inventory);

        return $this;
    }

    // -------------------------
    // Security cleanup
    // -------------------------

    /**
     * Очистка временных чувствительных данных.
     */
    public function eraseCredentials(): void
    {
        // Здесь можно очищать временные чувствительные данные,
        // если они будут добавлены в будущем.
    }
}
