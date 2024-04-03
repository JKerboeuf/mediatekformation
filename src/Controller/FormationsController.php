<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\PlaylistRepository;
use App\Entity\Formation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controleur des formations
 *
 * @author emds
 */
class FormationsController extends AbstractController
{
    /**
     * @var string Chemin vers le template html.twig de la page des formations
     */
    private const PAGE_FORMATIONS = 'pages/formations.html.twig';

    /**
     * @var string Chemin vers le template html.twig de la page de gestion des formations
     */
    private const PAGE_ADM_FORMATIONS = 'pages/admin/formations.html.twig';

    /**
     * @var FormationRepository
     */
    private $formationRepository;

    /**
     * @var CategorieRepository
     */
    private $categorieRepository;

    /**
     * @var PlaylistRepository
     */
    private $playlistRepository;

    public function __construct(
        FormationRepository $formationRepository,
        CategorieRepository $categorieRepository,
        PlaylistRepository $playlistRepository
    ) {
        $this->formationRepository = $formationRepository;
        $this->categorieRepository = $categorieRepository;
        $this->playlistRepository = $playlistRepository;
    }

    /**
     * @Route("/formations", name="formations")
     * @return Response
     */
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/formations/tri/{champ}/{ordre}/{table}", name="formations.sort")
     * @param type $champ
     * @param type $ordre
     * @param type $table
     * @return Response
     */
    public function sort($champ, $ordre, $table = ""): Response
    {
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/formations/recherche/{champ}/{table}", name="formations.findallcontain")
     * @param type $champ
     * @param Request $request
     * @param type $table
     * @return Response
     */
    public function findAllContain($champ, Request $request, $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table
        ]);
    }

    /**
     * @Route("/formations/formation/{id}", name="formations.showone")
     * @param type $id
     * @return Response
     */
    public function showOne($id): Response
    {
        $formation = $this->formationRepository->find($id);
        return $this->render("pages/formation.html.twig", [
            'formation' => $formation
        ]);
    }

    /**
     * @Route("/admin/formations", name="admin.formations")
     * @return Response
     */
    public function adminIndex(): Response
    {
        $formations = $this->formationRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADM_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/admin/formations/tri/{champ}/{ordre}/{table}", name="admin.formations.sort")
     * @param type $champ
     * @param type $ordre
     * @param type $table
     * @return Response
     */
    public function adminSort($champ, $ordre, $table = ""): Response
    {
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADM_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/admin/formations/recherche/{champ}/{table}", name="admin.formations.findallcontain")
     * @param type $champ
     * @param Request $request
     * @param type $table
     * @return Response
     */
    public function adminFindAllContain($champ, Request $request, $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADM_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table
        ]);
    }

    /**
     * @Route("/admin/formations/addedit/{id}", name="admin.formations.add-edit", methods={"GET"})
     * @param Int $id
     * @return Response
     */
    public function adminAddEditForm($id = null): Response
    {
        $formation = $id === null ? new Formation() : $this->formationRepository->find($id);
        $playlists = $this->playlistRepository->findAll();

        $form = $this->createFormBuilder($formation)
            ->add('title', TextType::class)
            ->add('published_at', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['max' => (new \DateTime())->format('Y-m-d\TH:i:s')]
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('video_id', TextType::class)
            ->add('playlist', ChoiceType::class, [
                'choices' => $playlists,
                'choice_value' => 'id',
                'choice_label' => 'name',
                'label' => 'Playlist'
            ])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
            ->getForm();

        return $this->render("forms/admin-form.html.twig", [
            'form' => $form->createView(),
            'formation' => $formation,
            'titre' => $id === null ? "Ajout d'un formulaire" : "Modification d'un formulaire"
        ]);
    }

    /**
     * @Route("/admin/formations/delete/{id}", name="admin.formations.delete", methods={"GET"})
     * @param Int $id
     * @return Response
     */
    public function adminDeleteForm($id): Response
    {
        $formation = $id === null ? new Formation() : $this->formationRepository->find($id);

        $form = $this->createFormBuilder($formation)
            ->add('delete', SubmitType::class, ['label' => 'Supprimer'])
            ->getForm();

        return $this->render("forms/admin-form.html.twig", [
            'form' => $form->createView(),
            'formation' => $formation,
            'titre' => 'Êtes-vous sûr de vouloir supprimer cette formation ?'
        ]);
    }
}
