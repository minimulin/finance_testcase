<?php

namespace FinanceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FinanceBundle\Entity\Share;
use FinanceBundle\Form\ShareType;

/**
 * Share controller.
 *
 * @Route("/share")
 */
class ShareController extends Controller
{

    /**
     * Lists all Share entities.
     *
     * @Route("/", name="share")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FinanceBundle:Share')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Share entity.
     *
     * @Route("/", name="share_create")
     * @Method("POST")
     * @Template("FinanceBundle:Share:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Share();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('share_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Share entity.
     *
     * @param Share $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Share $entity)
    {
        $form = $this->createForm(new ShareType(), $entity, array(
            'action' => $this->generateUrl('share_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('Create',[],'app')));

        return $form;
    }

    /**
     * Displays a form to create a new Share entity.
     *
     * @Route("/new", name="share_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Share();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Share entity.
     *
     * @Route("/{id}", name="share_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FinanceBundle:Share')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Share entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Share entity.
     *
     * @Route("/{id}/edit", name="share_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FinanceBundle:Share')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Share entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Share entity.
    *
    * @param Share $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Share $entity)
    {
        $form = $this->createForm(new ShareType(), $entity, array(
            'action' => $this->generateUrl('share_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('Update',[],'app')));

        return $form;
    }
    /**
     * Edits an existing Share entity.
     *
     * @Route("/{id}", name="share_update")
     * @Method("PUT")
     * @Template("FinanceBundle:Share:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FinanceBundle:Share')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Share entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('share_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Share entity.
     *
     * @Route("/{id}", name="share_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FinanceBundle:Share')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Share entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('share'));
    }

    /**
     * Creates a form to delete a Share entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('share_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('Delete',[],'app')))
            ->getForm()
        ;
    }
}
