<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 * @Vich\Uploadable
 */
class Image
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="product_image", fileNameProperty="imageName")
     *
     * @var File
     */
    private $imageFile;
    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    private $imageName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="image")
     */
    private $imageproducts;

    public function __construct()
    {
        $this->imageproducts = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }
    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }
    public function getImageName(): ?string
    {
        return $this->imageName;
    }
    public function __toString()
    {
        return (string) $this->getImageName();
    }

    /**
     * @return Collection|Product[]
     */
    public function getImageproducts(): Collection
    {
        return $this->imageproducts;
    }

    public function addImageproduct(Product $imageproduct): self
    {
        if (!$this->imageproducts->contains($imageproduct)) {
            $this->imageproducts[] = $imageproduct;
            $imageproduct->setImage($this);
        }

        return $this;
    }

    public function removeImageproduct(Product $imageproduct): self
    {
        if ($this->imageproducts->contains($imageproduct)) {
            $this->imageproducts->removeElement($imageproduct);
            // set the owning side to null (unless already changed)
            if ($imageproduct->getImage() === $this) {
                $imageproduct->setImage(null);
            }
        }

        return $this;
    }
}
