<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Utils\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use App\Service\FileUploader;

/**
 * @Route("/admin")
 */
class BlogPostController extends Controller
{
    /**
     * @Route("/", name="admin_index", methods="GET")
     */
    public function index(PostRepository $postRepository): Response
    {
        //$authorPosts = $posts->findBy(['author' => $this->getUser()], ['publishedAt' => 'DESC']);

        return $this->render('admin/index.html.twig', ['posts' => $postRepository->findAll()]);
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"}, name="admin_post_show", methods="GET")
     */
    public function show(Post $post): Response
    {
        //$this->denyAccessUnlessGranted('show', $post, 'Posts can only be shown to their authors.');
        return $this->render('admin/show.html.twig', ['post' => $post]);
    }

    /**
     * @Route("/new", name="admin_post_new", methods="GET|POST")
     * @param Request $request
<<<<<<< HEAD
=======
     * @param ImageUpload $imageUpload
>>>>>>> origin/master
     * @return Response
     */
    public function new(Request $request, FileUploader $fileUploader): Response
    {
        $post = new Post();
        $post->setAuthor($this->getUser());

        $form = $this->createForm(PostType::class, $post)
        ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug(Slugger::slugify($post->getTitle()));

            $file = $post->getImage();
            $fileName = $fileUploader->upload($file);

            $post->setImage($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'post.created_successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_post_new');
            }

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }


    /**
     * @Route("/{id}/edit", name="post_edit", methods="GET|POST")
     *  @Security("is_granted('delete', post)")
     */
    public function edit(Request $request, Post $post): Response
    {
        $this->denyAccessUnlessGranted('edit', $post, 'Posts can only be edited by their authors.');

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug(Slugger::slugify($post->getTitle()));
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'post.updated_successfully');

            return $this->redirectToRoute('post_edit', ['id' => $post->getId()]);
        }

        return $this->render('admin/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="post_delete", methods="DELETE")
     */
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_post_index');
        }
            $post->getTags()->clear();

            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();
        $this->addFlash('success', 'post.deleted_successfully');

        return $this->redirectToRoute('admin_post_index');
    }
}
