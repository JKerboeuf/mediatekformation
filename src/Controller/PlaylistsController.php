<?php

namespace App\Controller;

use App\Entity\Playlist;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\PlaylistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Description of PlaylistsController
 *
 * @author emds
 */
class PlaylistsController extends AbstractController
{
    /**
     * @var string Chemin vers le template html.twig de la page des playlists
     */
    private const PAGE_PLAYLISTS = 'pages/playlists.html.twig';

    /**
     * @var string Chemin vers le template html.twig de la page de gestion des playlists
     */
    private const PAGE_ADM_PLAYLISTS = 'pages/admin/playlists.html.twig';

    /**
     * @var PlaylistRepository
     */
    private $playlistRepository;

    /**
     * @var FormationRepository
     */
    private $formationRepository;

    /**
     * @var CategorieRepository
     */
    private $categorieRepository;

    public function __construct(
        PlaylistRepository $playlistRepository,
        CategorieRepository $categorieRepository,
        FormationRepository $formationRespository
    ) {
        $this->playlistRepository = $playlistRepository;
        $this->categorieRepository = $categorieRepository;
        $this->formationRepository = $formationRespository;
    }

    /**
     * @Route("/playlists", name="playlists")
     * @return Response
     */
    public function index(): Response
    {
        $playlists = $this->playlistRepository->findAllOrderByName('ASC');
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_PLAYLISTS, [
            'playlists' => $playlists,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/playlists/tri/{champ}/{ordre}", name="playlists.sort")
     * @param type $champ
     * @param type $ordre
     * @return Response
     */
    public function sort($champ, $ordre): Response
    {
        switch ($champ) {
            case 'name':
                $playlists = $this->playlistRepository->findAllOrderByName($ordre);
                break;
            case 'formations':
                $playlists = $this->playlistRepository->findAllOrderByName('ASC');
                $this->sortPlaylists($playlists, $ordre);
                break;
            default:
                break;
        }
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_PLAYLISTS, [
            'playlists' => $playlists,
            'categories' => $categories
        ]);
    }

    private function sortPlaylists(array &$playlists, $ordre)
    {
        switch ($ordre) {
            case 'DESC':
                usort($playlists, function ($a, $b) {
                    $countA = count($a->getFormations());
                    $countB = count($b->getFormations());

                    if ($countA == $countB) {
                        return 0;
                    }
                    return ($countA > $countB) ? -1 : 1;
                });
                break;
            case 'ASC':
                usort($playlists, function ($a, $b) {
                    $countA = count($a->getFormations());
                    $countB = count($b->getFormations());

                    if ($countA == $countB) {
                        return 0;
                    }
                    return ($countA < $countB) ? -1 : 1;
                });
                break;
            default:
                break;
        }
    }

    /**
     * @Route("/playlists/recherche/{champ}/{table}", name="playlists.findallcontain")
     * @param type $champ
     * @param Request $request
     * @param type $table
     * @return Response
     */
    public function findAllContain($champ, Request $request, $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $playlists = $this->playlistRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_PLAYLISTS, [
            'playlists' => $playlists,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table
        ]);
    }

    /**
     * @Route("/playlists/playlist/{id}", name="playlists.showone")
     * @param type $id
     * @return Response
     */
    public function showOne($id): Response
    {
        $playlist = $this->playlistRepository->find($id);
        $playlistCategories = $this->categorieRepository->findAllForOnePlaylist($id);
        $playlistFormations = $this->formationRepository->findAllForOnePlaylist($id);
        return $this->render("pages/playlist.html.twig", [
            'playlist' => $playlist,
            'playlistcategories' => $playlistCategories,
            'playlistformations' => $playlistFormations
        ]);
    }

    /**
     * @Route("/admin/playlists", name="admin.playlists")
     * @return Response
     */
    public function adminIndex(): Response
    {
        $playlists = $this->playlistRepository->findAllOrderByName('ASC');
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADM_PLAYLISTS, [
            'playlists' => $playlists,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/admin/playlists/tri/{champ}/{ordre}", name="admin.playlists.sort")
     * @param type $champ
     * @param type $ordre
     * @return Response
     */
    public function adminSort($champ, $ordre): Response
    {
        switch ($champ) {
            case 'name':
                $playlists = $this->playlistRepository->findAllOrderByName($ordre);
                break;
            case 'formations':
                $playlists = $this->playlistRepository->findAllOrderByName('ASC');
                $this->sortPlaylists($playlists, $ordre);
                break;
            default:
                break;
        }
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADM_PLAYLISTS, [
            'playlists' => $playlists,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/admin/playlists/recherche/{champ}/{table}", name="admin.playlists.findallcontain")
     * @param type $champ
     * @param Request $request
     * @param type $table
     * @return Response
     */
    public function adminFindAllContain($champ, Request $request, $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $playlists = $this->playlistRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADM_PLAYLISTS, [
            'playlists' => $playlists,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table
        ]);
    }

    /**
     * @Route("/admin/playlists/addedit/{id}", name="admin.playlists.add-edit", methods={"GET"})
     * @param Int $id
     * @return Response
     */
    public function adminAddEditForm($id = null): Response
    {
        $playlist = $id === null ? new Playlist() : $this->playlistRepository->find($id);

        $form = $this->createFormBuilder($playlist)
            ->add('name', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
            ->getForm();

        return $this->render("forms/admin-form.html.twig", [
            'form' => $form->createView(),
            'playlist' => $playlist,
            'titre' => $id === null ? "Ajout d'un formulaire" : "Modification d'un formulaire"
        ]);
    }

    /**
     * @Route("/admin/playlists/delete/{id}", name="admin.playlists.delete", methods={"GET"})
     * @param Int $id
     * @return Response
     */
    public function adminDeleteForm($id): Response
    {
        $playlist = $id === null ? new Playlist() : $this->playlistRepository->find($id);

        $form = $this->createFormBuilder($playlist)
            ->add('delete', SubmitType::class, ['label' => 'Supprimer'])
            ->getForm();

        return $this->render("forms/admin-form.html.twig", [
            'form' => $form->createView(),
            'playlist' => $playlist,
            'titre' => 'Êtes-vous sûr de vouloir supprimer cette playlist ?'
        ]);
    }
}
