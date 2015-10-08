<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Share;
use AppBundle\Form\ShareType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Share controller.
 *
 * @Route("/share")
 */
class ShareController extends Controller
{

    /**
     * Список всех акций
     *
     * @Route("/", name="share")
     * @Method("GET")
     */
    public function indexAction()
    {
        $entities = $this->getDoctrine()
            ->getRepository('AppBundle:Share')
            ->findAll();

        return $this->render('views/share/index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Отображает форму создания новой акции
     *
     * @Route("/new", name="share_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function newAction(Request $request)
    {
        $entity = new Share();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('share_show', array('id' => $entity->getId())));
        }

        return $this->render('views/share/new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Возвращает форму для создания новой акции
     *
     * @param Share $entity Акция
     *
     * @return \Symfony\Component\Form\Form Форма
     */
    protected function createCreateForm(Share $entity)
    {
        $form = $this->createForm(new ShareType(), $entity, array(
            'action' => $this->generateUrl('share_new'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Отображает акцию
     *
     * @Route("/{id}", name="share_show")
     * @Method("GET")
     * @ParamConverter("entity", class="AppBundle:Share")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function showAction(Share $entity, $id)
    {
        if (!$entity) {
            throw $this->createNotFoundException('entity.found.error');
        }

        return $this->render('views/share/show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $this->createDeleteForm($id)->createView(),
        ));
    }

    /**
     * Форма редактирования акции
     *
     * @Route("/{id}/edit", name="share_edit")
     * @Method({"GET", "PUT"})
     * @ParamConverter("entity", class="AppBundle:Share")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function editAction(Request $request, Share $entity, $id)
    {
        if (!$entity) {
            throw $this->createNotFoundException('entity.found.error');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($this->generateUrl('share_edit', array('id' => $id)));
        }

        return $this->render('views/share/edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $this->createDeleteForm($id)->createView(),
        ));
    }

    /**
     * Возвращает форму создания акции
     *
     * @param Share $entity Акция
     *
     * @return \Symfony\Component\Form\Form Форма
     */
    protected function createEditForm(Share $entity)
    {
        $form = $this->createForm(new ShareType(), $entity, array(
            'action' => $this->generateUrl('share_edit', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        return $form;
    }

    /**
     * Удаляет акцию
     *
     * @Route("/{id}", name="share_delete")
     * @Method("DELETE")
     * @ParamConverter("entity", class="AppBundle:Share")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function deleteAction(Request $request, Share $entity, $id)
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

        return $this->redirect($this->generateUrl('share'));
    }

    /**
     * Создает форму удаления акции
     *
     * @param mixed $id Идентификатор акции
     *
     * @return \Symfony\Component\Form\Form Форма
     */
    protected function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('share_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('delete')))
            ->getForm()
        ;
    }
}
