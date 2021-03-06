<?php


namespace App\Controller;


use App\Form\ArticleSearchType;
use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Article;
use App\Entity\Category;

/**
 * Class BlogController
 * @package App\Controller
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    /**
     * @Route("/", name="blog_index")
     */
    public function index(Request $request): Response
    {
        $category = new Category();
        $categoryform = $this->createForm(
            CategoryType::class,
            $category);


        $form = $this->createForm(
            ArticleSearchType::class,
            null,
            ['method' => Request::METHOD_GET]
        );



        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $articles = $this->getDoctrine()->getRepository(Article::class)->findAll();
        if (!$articles) {
            throw  $this->createNotFoundException('No article found in article\'s table.');
        }

        return $this->render('blog/index.html.twig', ['articles' => $articles, 'categories' => $categories,
            'form' => $form->createView(),
            'categoryform' => $categoryform->createView()]);
    }

    /**
     * @Route("/page/{page}", name="blog_list")
     */
    public function list($page)
    {
        return $this->render('blog/list.html.twig', ['page' => $page]);
    }

    /**
     * Getting a article with a formatted slug for title
     *
     * @param string $slug The slugger
     *
     * @Route("/{slug<^[a-z0-9-]+$>}",
     *     defaults={"slug" = null},
     *     name="blog_show")
     * @return Response A response instance
     */
    public
    function show(?string $slug): Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find an article in article\'s table.');
        }

        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );

        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);

        if (!$article) {
            throw $this->createNotFoundException(
                'No article with ' . $slug . ' title, found in article\'s table.'
            );
        }
        $category = $article->getCategory();
        return $this->render(
            'blog/show.html.twig',
            [
                'article' => $article,
                'slug' => $slug,
                'category' => $category
            ]
        );
    }

    /**
     *
     * @return Response
     * @Route("/category/{name}", name="show_category")
     */
    public function showByCategory(Category $category)
    {
        $articles = $category->getArticles();

        return $this->render(
            'blog/category.html.twig', ['categoryArticles' => $articles, 'category' => $category]
        );
    }
}

