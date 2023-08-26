<?php

namespace App\Entity;

use App\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: 'books')]
#[ORM\HasLifecycleCallbacks()]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $publicationDate = null;

    #[ORM\ManyToMany(targetEntity: Author::class, mappedBy: 'books', cascade: ['persist'])]
    private Collection $authors;

    public function __construct(
        string $name,
        ?string $description,
        ?DateTimeImmutable $publicationDate,
        Author ...$authors
    )
    {
        if (empty($authors)) {
            throw new DomainException('Book must have min 1 author');
        }
        $this->name = $name;
        $this->description = $description;
        $this->publicationDate = $publicationDate;
        foreach ($authors as $author) {
            $author->addBook($this);
        }
        $this->authors = new ArrayCollection($authors);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function updateName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function updateDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPublicationDate(): ?DateTimeImmutable
    {
        return $this->publicationDate;
    }

    public function updatePublicationDate(?DateTimeImmutable $date): self
    {
        $this->publicationDate = $date;

        return $this;
    }

    /**
     * @return Collection<int, Author>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): static
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
            $author->addBook($this);
        }

        return $this;
    }

    public function removeAuthor(Author $author): static
    {
        if ($this->authors->contains($author) && $this->authors->count() === 1) {
            throw new DomainException('Book must have min 1 author');
        }
        if ($this->authors->removeElement($author)) {
            $author->removeBook($this);
        }

        return $this;
    }
}
