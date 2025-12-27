<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Inventory;
use App\Entity\InventoryAccess;
use App\Entity\User;
use App\Repository\InventoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class InventoryVoter extends Voter
{
    public const VIEW = 'INVENTORY_VIEW';
    public const EDIT = 'INVENTORY_EDIT';
    public const MANAGE_FIELDS = 'INVENTORY_MANAGE_FIELDS';

    public function __construct(
        private InventoryAccessRepository $accessRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Inventory
            && in_array($attribute, [
                self::VIEW,
                self::EDIT,
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

        // 1. Owner — always full access
        if ($inventory->getOwner()->getId() === $user->getId()) {
            return true;
        }

        // 2. Public inventory — view only
        if ($attribute === self::VIEW && $inventory->isPublic()) {
            return true;
        }

        // 3. ACL-based access
        $access = $this->accessRepository->findOneBy([
            'inventory' => $inventory,
            'user' => $user,
        ]);

        if (!$access instanceof InventoryAccess) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => true,
            self::EDIT,
            self::MANAGE_FIELDS => $access->getPermission() === InventoryAccess::WRITE,
            default => false,
        };
    }
}
