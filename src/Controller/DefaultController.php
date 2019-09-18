<?php

namespace App\Controller;

use App\Entity\Invorder;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Invoice;
use App\Form\CheckoutCartType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends AbstractController
{
    private $session;
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="default")
     */
    public function index()
    {

        $products = $this->getDoctrine()->getRepository(Product::class)->findall();
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'products' => $products,
        ]);
    }

    /**
     * @Route("/cart/{id}", name="product_tocart", methods={"GET","POST"})
     */
    public function AddToCart(Product $product)
    {
        $getCart = $this->session->get('cart', []);
        if(isset($getCart[$product->getId()])){
            $getCart[$product->getId()]['aantal']++;
        }
        else{
            $getCart[$product->getId()] = array(
                'aantal' => 1,
                'name' =>$product->getName(),
                'price' => $product->getPrice(),
                'id' =>$product->getId(),
            );
        }
        $this->session->set('cart', $getCart);
        var_dump($this->session->get('cart'));
        return $this->render('cart/cart.html.twig',[
            'product' => $getCart[$product->getId()]['name'],
            'aantal' => $getCart[$product->getId()]['aantal'],
            'cart' => $getCart
        ]);
    }
    /**
     * @Route("/cartcheckout/", name="checkout")
     */
    public function checkout()
    {
        $session = $this->get('request_stack')->getCurrentRequest()->getSession();
        $cart = $session->get('cart', array());

        $em = $this->getDoctrine()->getManager();
        $userAdress = $em->getRepository('App:User')->findOneBy(array('id' => $this->getUser()->getId()));

        $factuur = new Invoice();
        $factuur->setUser($this->getUser());
        $factuur->setOrderdate(new \DateTime("now"));
        $factuur->setPaid(true);
        $factuur->setPaidDate(new \DateTime("now"));

        if (isset($cart)) {
            $em->persist($factuur);
            $em->flush();

            foreach ($cart as $id => $quantity) {
                $row = new Invorder();
                $row->setInvoice($factuur);

                $em = $this->getDoctrine()->getManager();
                $product = $em->getRepository('App:Product')->find($id);

                $row->setQuantity($quantity['aantal']);
                $row->setProduct($product);

                $em = $this->getDoctrine()->getManager();
                $em->persist($row);
                $em->flush();

            }
        }
        $session->clear();
        $products = $this->getDoctrine()->getRepository(Product::class)->findall();
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'products' => $products,
        ]);
    }
    /**
     * @Route("/profile/dashboard", name="myOrders")
     */
    public function yourBookings(){
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $invoice = $this->getDoctrine()->getRepository(Invoice::class)->findBy(['user' => $user]);
        return $this->render('profile/profile.html.twig', [
            'invoice' => $invoice,
        ]);
    }
}
