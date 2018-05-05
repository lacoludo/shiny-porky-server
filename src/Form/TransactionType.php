<?php 
namespace App\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'Statut de la transaction',
                'choices' => [
                    'Paiement reçu' => 'Paiement reçu',
                    'Achat de l\'or en cours' => 'Achat de l\'or en cours',
                    'En cours de transition vers le coffre' => 'En cours de transition vers le coffre',
                    'Terminé' => 'Terminé',
                ]
            ])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }
}