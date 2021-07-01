<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="date")
     */
    private $public_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @ORM\ManyToMany(targetEntity=Author::class, mappedBy="books")
     */
    private $authors;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        dump("setTitle");
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPublicAt(): ?\DateTimeInterface
    {
        return $this->public_at;
    }

    public function setPublicAt(\DateTimeInterface $public_at): self
    {
        dump("SetPublicAt");
        $this->public_at = $public_at;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return Collection|Author[]
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): self
    {
        if (!$this->authors->contains($author)) {
            $this->authors[] = $author;
            $author->addBook($this);            
        }

        return $this;
    }

    public function removeAuthor(Author $author): self
    {
        // if ($this->authors->removeElement($book)) {
        $this->authors->removeElement($author);
        // }

        return $this;
    }

    public function removeAuthors(): self
    {
        foreach ($this->authors as $author) {
            $this->authors->removeElement($author);
        }
        return $this;
    }

    /**
     * Unmapped property for fileuploads
     */
    private $pictureFile;

    /**
     * @param UploadedFile $pictureFile
     */
    public function setPictureFile(UploadedFile $pictureFile = null): self
    {
        $this->pictureFile = $pictureFile;
        $this->upload();

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getPictureFile()
    {
        return $this->pictureFile;
    }

    /**
     * Copy from tmp and save pictureFile
     */
    public function upload()
    {
        if (null === $this->getPictureFile()) {
            return;
        }

        $originalFilename = pathinfo($this->getPictureFile()->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$this->getPictureFile()->guessExtension();

        $this->getPictureFile()->move(
            'uploads/pictures',
            $newFilename
        );

        $this->setPicture($newFilename);

        $this->setPictureFile(null);
    }
}
