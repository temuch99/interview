<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Route("/book")
 */
class BookController extends AbstractController
{
    /**
     * @Route("/", name="book_index", methods={"GET"})
     */
    public function index(Request $request, BookRepository $bookRepository, AuthorRepository $authorRepository): Response
    {
        $criterias = array_filter($request->query->all(), function($field) {
            return boolval($field);
        });

        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findBy($criterias),
            'authors' => $authorRepository->findAll(),
            'criterias' => $criterias
        ]);
    }

    /**
     * @Route("/doctrine", name="book_index_doctrine", methods={"GET"})
     */
    public function indexDoctrine(Request $request, BookRepository $bookRepository, AuthorRepository $authorRepository): Response
    {
        $books = $bookRepository->findWhereMoreThanTwoAuthors();
        return $this->render('book/index.html.twig', [
            'books' => $books,
            'authors' => $authorRepository->findAll(),
        ]);
    }

    /**
     * @Route("/sql", name="book_index_sql", methods={"GET"})
     */
    public function indexSQL(Request $request, BookRepository $bookRepository, AuthorRepository $authorRepository): Response
    {
        $booksArray = $bookRepository->findWhereMoreThanTwoAuthorsSQL();
        $books = [];
        foreach ($booksArray as $book) {
            $books[] = $bookRepository->find($book['id']);
        }

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'authors' => $authorRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="book_new", methods={"GET","POST"})
     */
    public function new(Request $request, AuthorRepository $authorRepository): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book, [
            'require_picture' => true
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $pictureFile */
            $pictureFile = $form->get('picture')->getData();
            
            $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = $originalFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

            try {
                $pictureFile->move(
                    $this->getParameter('pictures_directory'),
                    $newFilename
                );
            } catch (FileException $e) {}

            $book->setPicture($newFilename); 

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($book);

            foreach($book->getAuthors() as $author) {
                $author->addBook($book);
                $entityManager->persist($author);
                $entityManager->flush();
            }

            $entityManager->flush();

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="book_show", methods={"GET"})
     */
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="book_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Book $book, AuthorRepository $authorRepository): Response
    {
        $form = $this->createForm(BookType::class, $book, [
            'require_picture' => false
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            if ($form->get('picture')->getData() != "") {
                $pictureFile = $form->get('picture')->getData();

                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {}
                
                $book->setPicture($newFilename); 
            }

            $entityManager = $this->getDoctrine()->getManager();

            $originAuthors = new ArrayCollection();
            foreach($book->getAuthors() as $author) {
                $originAuthors[] = $author;
            }

            $allauthors = $authorRepository->findAll();
            foreach ($allauthors as $author) {
                $author->removeBook($book);
                $entityManager->persist($author);
                $entityManager->flush();
            }

            foreach ($originAuthors as $author) {
                $author->addBook($book);
                $entityManager->persist($author);
                $entityManager->flush();
            }

            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="book_delete", methods={"POST"})
     */
    public function delete(Request $request, Book $book): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('book_index');
    }
}
