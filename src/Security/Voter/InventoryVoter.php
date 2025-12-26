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
    public const VIEW            = 'INVENTORY_VIEW';
    public const CREATE_ITEM     = 'INVENTORY_CREATE_ITEM';
    public const EDIT_ITEM       = 'INVENTORY_EDIT_ITEM';
    public const DELETE_ITEM     = 'INVENTORY_DELETE_ITEM';
    public const MANAGE_ACCESS   = 'INVENTORY_MANAGE_ACCESS';

    public function __construct(
        private InventoryAccessRepository $accessRepository
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Inventory
            && \in_array($attribute, [
                self::VIEW,
                self::CREATE_ITEM,
                self::EDIT_ITEM,
                self::DELETE_ITEM,
                self::MANAGE_ACCESS,
            ], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var Inventory $inventory */
        $inventory = $subject;
        $user = $token->getUser();

        // 1) Guest
        if (!$user instanceof User) {
            return $attribute === self::VIEW && $inventory->isPublic();
        }

        // 2) Admin
        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        // 3) Owner
        if ($inventory->getOwner()->getId() === $user->getId()) {
            return true;
        }

        // 4) ACL
        $access = $this->accessRepository->findOneBy([
            'inventory' => $inventory,
            'user' => $user,
        ]);

        if (!$access) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => true,
            self::CREATE_ITEM,
            self::EDIT_ITEM,
            self::DELETE_ITEM => $access->getPermission() === InventoryAccess::PERMISSION_WRITE,
            self::MANAGE_ACCESS => false,
            default => false,
        };
    }
}
