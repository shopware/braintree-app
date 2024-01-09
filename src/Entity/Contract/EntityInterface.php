<?php declare(strict_types=1);

namespace Swag\Braintree\Entity\Contract;

use Symfony\Component\Uid\Uuid;

interface EntityInterface
{
    public function getId(): ?Uuid;

    public function setId(?Uuid $id): self;

    public function getCreatedAt(): \DateTimeInterface;

    public function setCreatedAt(\DateTimeInterface $createdAt): self;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self;
}
