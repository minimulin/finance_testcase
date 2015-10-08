<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Portfolio;
use AppBundle\Form\PortfolioType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Portfolio controller.
 *
 * @Route("/portfolio")
 */
class PortfolioController extends Controller
{

    /**
     * Список всех портфелей
     *
     * @Route("/", name="portfolio")
     * @Method("GET")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function indexAction()
    {
        $entities = $this->getDoctrine()
            ->getRepository('AppBundle:Portfolio')
            ->findByUser($this->getUser());

        return $this->render('views/portfolio/index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Отображает форму создания нового портфеля
     *
     * @Route("/new", name="portfolio_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function newAction(Request $request)
    {
        $entity = new Portfolio();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid() && !empty($user)) {
            $em = $this->getDoctrine()->getManager();
            $entity->setUser($user);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('portfolio_show', array('id' => $entity->getId())));
        }

        return $this->render('views/portfolio/new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Возвращает форму для создания нового портфеля
     *
     * @param Portfolio $entity Портфель
     *
     * @return \Symfony\Component\Form\Form Форма
     */
    protected function createCreateForm(Portfolio $entity)
    {
        $form = $this->createForm(new PortfolioType(), $entity, array(
            'action' => $this->generateUrl('portfolio_new'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Отображает портфель
     *
     * @Route("/{id}", name="portfolio_show")
     * @Method("GET")
     * @ParamConverter("entity", class="AppBundle:Portfolio")
     */
    public function showAction(Portfolio $entity, $id)
    {
        if (!$entity) {
            throw $this->createNotFoundException($this->get('translator')->trans('entity.found.error'));
        }

        $this->checkUserCanAccessEntity($entity);

        list($share_names, $share_codes) = static::getSharesCodesAndNames($entity->getShares());
        $share_names[] = $translated = $this->get('translator')->trans('portfolio');

        $chartData = $this->get('yahoo.finance')->getDataWithSummary($share_codes);

        return $this->render('views/portfolio/show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $this->createDeleteForm($id)->createView(),
            'chart_data' => $chartData,
            'share_names' => $share_names,
        ));
    }

    /**
     * Возвращает массив кодов и наименований акций
     * @param  array|PersistentCollection $shares Коллекция или массив акций
     * @return array         Массив с наименованиями и кодами акций
     */
    protected static function getSharesCodesAndNames($shares)
    {
        $share_names = $share_codes = [];
        foreach ($shares as $share) {
            $share_names[] = $share->getName();
            $share_codes[] = $share->getCode();
        }
        return array($share_names, $share_codes);
    }

    /**
     * Форма редактирования портфеля
     *
     * @Route("/{id}/edit", name="portfolio_edit")
     * @Method({"GET", "PUT"})
     * @ParamConverter("entity", class="AppBundle:Portfolio")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function editAction(Request $request, Portfolio $entity, $id)
    {
        if (!$entity) {
            throw $this->createNotFoundException('entity.found.error');
        }

        $this->checkUserCanAccessEntity($entity);

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($this->generateUrl('portfolio_edit', array('id' => $id)));
        }

        return $this->render('views/portfolio/edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $this->createDeleteForm($id)->createView(),
        ));
    }

    /**
     * Возвращает форму редактирования портфеля
     *
     * @param Portfolio $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createEditForm(Portfolio $entity)
    {
        $form = $this->createForm(new PortfolioType(), $entity, array(
            'action' => $this->generateUrl('portfolio_edit', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        return $form;
    }

    /**
     * Удаляет портфель
     *
     * @Route("/{id}", name="portfolio_delete")
     * @Method("DELETE")
     * @ParamConverter("entity", class="AppBundle:Portfolio")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function deleteAction(Request $request, Portfolio $entity, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!$entity) {
                throw $this->createNotFoundException('entity.found.error');
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('portfolio'));
    }

    /**
     * Создает форму для удаления портфеля по его идентификатору
     *
     * @param mixed $id Идентификатор записи
     *
     * @return \Symfony\Component\Form\Form Форма
     */
    protected function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('portfolio_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('delete')))
            ->getForm();
    }

    protected function checkUserCanAccessEntity(Portfolio $entity)
    {
        $user = $this->getUser();
        //Пользователь может видеть только свои портфели
        if ($entity->getUser() != $user) {
            throw new AccessDeniedHttpException($this->get('translator')->trans('portfolio.access'));
        }
    }
}
