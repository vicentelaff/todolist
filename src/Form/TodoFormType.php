<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Todo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TodoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                "label" => "Titre",
                "empty_data" =>"",
                "attr" => [
                    "placeholder" => "Entrer titre"
                ]
            ])
            ->add('content', TextareaType::class, [
                "label" => "Contenu",
                "empty_data" =>"",
                "attr" => [
                    "placeholder" => "Entrer mémo"
                ]
            ]);
        if ($options["data"]->getId() == null) {
            $builder->add('deadline', DateType::class, [
                "label" => "A faire pour: ",
                "years" => ["2021", "2022"],
                "format" => "dd MM yyyy",
                "data" => new \DateTime("now", new \DateTimeZone("Europe/Paris"))
            ]);
        } else {
            $builder->add('deadline', DateType::class, [
                "label" => "A faire pour: ",
                "years" => ["2021", "2022"],
                "format" => "dd MM yyyy",
            ]);
        }
        $builder
            ->add('category', EntityType::class, [
                "label" => "Catégorie?",
                "class" => Category::class,
                "choice_label" => "name"
            ])
            ->add("submit", SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Todo::class,
            "attr" => [
                "novalidate" => "novalidate"
            ]
        ]);
    }
}
