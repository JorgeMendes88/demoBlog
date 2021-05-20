<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *      fields = {"email"},
 *      message = "Un compte est déjà existant à cette adresse Email."
 * )
 * 
 * @UniqueEntity(
 *      fields = {"username"},
 *      message = "Ce nom d'utilisateur est déjà existant, veuillez en saisir un nouveau."
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez saisir votre nom.")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez saisir votre prenom.")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez saisir votre adresse Email.")
     * @Assert\Email(message="Veuillez saisir une adresse Email valide.")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez saisir votre nom d'utilisateur.")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\EqualTo(
     *      propertyPath="confirm_password",
     *      message="Vérifier les mots de passe"
     * )
     */
    private $password;

    /**
     * Cette propriété permet uniquement de comparer les mots de passe, elle n'est pas mappé, pas inséré en BDD
     * @Assert\EqualTo(
     *      propertyPath="password",
     *      message="Vérifier les mots de passe"
     * )
     */
    public $confirm_password;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    // Renvoi la chaine de caractère non encodé que l'utilisateur a saisi, qui est utilisé à l'origine pour encoder le mot de passe
    public function getSalt()
    {
        
    }

    // cette méthode est uniquement destinée à nettoyer les mots de passe en texte brut éventuellement stockés
    public function eraseCredentials()
    {
        
    }

    // cette focntion renvoi un tableau de chaine de caractères
    // Renvoie les rôles accordés à l'utilisateur
    public function getRoles()
    {
        //return ["ROLE_USER"];
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
