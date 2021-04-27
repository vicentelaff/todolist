<?php

namespace App\Controller;

use App\Entity\Todo;
use App\Entity\Category;
use App\Form\TodoFormType;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\TodoRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TodoController extends AbstractController
{

    private $categories;

    function __construct(CategoryRepository $repo)
    {
        $this->categories = $repo->findAll();
    }


    #[Route('/', name: 'todo')]
    public function index(TodoRepository $repo): Response
    {
        $todo = $repo->findAll();
        return $this->render('todo/index.html.twig', [
            "todo" => $todo,
            "categories" => $this->categories
        ]);
    }



    #[Route('/details/{id}', name: 'details')]
    public function details($id, TodoRepository $repo): Response
    {
        $todo = $repo->find($id);
        // dd($todo);
        return $this->render('todo/details.html.twig', [
            "todo" => $todo,
            "categories" => $this->categories
        ]);
    }



    /**
     * @Route("/todo/create", name="create", methods={"GET", "POST"})
     *
     * @return void
     */
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        // Etape affichage (GET):
        $todo = new Todo;
        $form = $this->createForm(TodoFormType::class, $todo);
        $form->handleRequest($request);

        // Etape soumission (POST):
        if ($form->isSubmitted() && $form->isValid()) {
            // Méthode ancienne:
            // $this->getDoctrine()->getManager()->persist($todo);
            // Méthode optimale:
            $em->persist($todo);
            $em->flush();
            return $this->redirectToRoute("todo");
        }

        return $this->render('todo/create.html.twig', [
            "formTodo" => $form->createView(),
            "categories" => $this->categories
        ]);
    }

    /**
     * paramconverter => correspondance entre un ID dans la route et un objet du type Todo
     * @Route("/todo/{id}/edit", name="edit", methods={"GET","POST"})
     * 
     */
    public function edit(Todo $todo, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TodoFormType::class, $todo);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            # Update
            $todo->setUpdatedAt(new \DateTime("now"));
            $em->flush();

            # Créer msg flash (session flash)
            $this->addFlash(
                "info",
                "Modifications enregistrées avec succès !"
            );

            # On revient sur la même page (GET)
            return $this->redirectToRoute("edit", ["id" => $todo->getId()]);
        }

        return $this->render('todo/edit.html.twig', [
            "formTodo" => $form->createView(),
            "todo" => $todo,
            "categories" => $this->categories
        ]);
    }

    /**
     * @Route("/todo/{id}/delete", name="delete")
     *
     * @return void
     */
    public function delete(Todo $todo, EntityManagerInterface $em)
    {
        $em->remove($todo);
        $em->flush();
        return $this->redirectToRoute("todo");
    }

    /**
     * @Route("/todo/{id}/delete_csrf", name="delete_csrf", methods={"DELETE"})
     *
     * 
     * $request->request->get()   :    POST
     * $request->query->get()   :    GET
     * 
     * @return void
     */
    public function delete2(Todo $todo, EntityManagerInterface $em, Request $request)
    {

        $submittedToken = $request->request->get("token");
        // dd($submittedToken);
        if ($this->isCsrfTokenValid("delete-item", $submittedToken)) {
            $em->remove($todo);
            $em->flush();
        }

        return $this->redirectToRoute("todo");
    }

    /**
     * @Route("/todos/category/{id}", name="todo_category")
     *
     * @param Category $cat
     * @return void
     */
    public function todoByCategory(Category $cat): Response
    {
        return $this->render("todo/index.html.twig", [
            "todo" => $cat->getTodos(),
            "categories" => $this->categories
        ]);
    }
}
