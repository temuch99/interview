<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Book;
use App\Entity\Author;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\File;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $authorNames = array("Анатолий", "Виктор", "Кирилл", "Александр", "Сергей", "Шрек");
        $authorSurnames = array("Иванов", "Петров", "Коровин", "Кузнецов", "Романов", "Кириллов");
        $authors = [];

        for ($i = 0; $i < 10; $i++) {
            $author = new Author();
            $author->setName($authorNames[array_rand($authorNames)]); 
            $author->setSurname($authorSurnames[array_rand($authorSurnames)]);

            $manager->persist($author);
            $authors[] = $author;
        }

        $bookTitles = array("Лабиринты разума", "Война и Мир", "Преступление и наказание", "Маленькая елочка", "Игра престолов", "Заголовок книги");
        $bookDescriptions = array("Клевая книга", "Скучная книга", "Детектив", "Триллер", "Ужасы", "Поэма", "Роман");

        for ($i = 0; $i < 10; $i++) {
        	$book = new Book();
        	$book->setTitle($bookTitles[array_rand($bookTitles)]);
        	$book->setDescription($bookDescriptions[array_rand($bookDescriptions)]);
        	$book->setPublicAt(new \DateTime());
        	$book->setPicture("msu.jpg");

        	$authorCount = rand(1, 3);
        	$bookAuthors = array_rand($authors, $authorCount);
        	
        	if (gettype($bookAuthors) == "array") {
	        	foreach ($bookAuthors as $bookAuthor) {
	        		$book->addAuthor($authors[$bookAuthor]);
	        		$authors[$bookAuthor]->addBook($book);
	        		$manager->persist($authors[$bookAuthor]);
	        	}
	        }
	        else {
	        	$book->addAuthor($authors[$bookAuthors]);
        		$authors[$bookAuthors]->addBook($book);
        		$manager->persist($authors[$bookAuthors]);
	        }

        	$manager->persist($book);
        }

        $manager->flush();
    }
}
