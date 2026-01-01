<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Inventory;
use App\Entity\InventoryAccess;
use App\Entity\User;
use App\Repository\InventoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * InventoryVoter
 *
 * Правила доступа:
 * - ADMIN  : полный доступ ко всем инвентарям
 * - OWNER  : полный доступ к своим инвентарям
 * - PUBLIC : доступ только на VIEW
 * - ACL    :
 *     - VIEW  : разрешён
 *     - EDIT  : разрешён при WRITE
 *     - DELETE: ЗАПРЕЩЁН
 *     - MANAGE_FIELDS: ЗАПРЕЩЁН
 *
 * DELETE и MANAGE_FIELDS — деструктивные операции,
 * разрешены ТОЛЬКО владельцу или администратору.
 */
final class InventoryVoter extends Voter
{
    public const VIEW = 'INVENTORY_VIEW';
    public const EDIT = 'INVENTORY_EDIT';
    public const DELETE = 'INVENTORY_DELETE';
    public const MANAGE_FIELDS = 'INVENTORY_MANAGE_FIELDS';

    public function __construct(
        private readonly InventoryAccessRepository $accessRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Inventory
            && \in_array($attribute, [
                self::VIEW,
                self::EDIT,
                self::DELETE,
                self::MANAGE_FIELDS,
            ], true);
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Inventory $inventory */
        $inventory = $subject;

        // 1. Администратор — полный доступ
        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        // 2. Владелец — полный доступ
        if ($inventory->getOwner()->getId() === $user->getId()) {
            return true;
        }

        // 3. Публичный инвентарь — только VIEW
        if ($attribute === self::VIEW && $inventory->isPublic()) {
            return true;
        }

        // 4. ACL (InventoryAccess)
        $access = $this->accessRepository->findOneBy([
            'inventory' => $inventory,
            'user' => $user,
        ]);

        if (!$access instanceof InventoryAccess) {
            return false;
        }

        // 5. Ограниченные права по ACL
        return match ($attribute) {
            self::VIEW => true,

            self::EDIT =>
                $access->getPermission() === InventoryAccess::PERMISSION_WRITE,

            // DELETE и MANAGE_FIELDS запрещены через ACL
            self::DELETE,
            self::MANAGE_FIELDS => false,

            default => false,
        };
    }
}
