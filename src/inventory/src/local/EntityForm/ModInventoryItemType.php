<?php

namespace Lotgd\Local\EntityForm;

use LotgdSetting;
use Doctrine;
use Lotgd\Core\Form\Type\BitFieldType;
use Lotgd\Local\Entity\ModInventoryItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckBoxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModInventoryItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('class', TextType::class, [
                'label' => 'form.item.class',
            ])
            ->add('name', TextType::class, [
                'label' => 'form.item.name',
            ])
            ->add('image', TextType::class, [
                'label' => 'form.item.image',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'form.item.description',
            ])
            ->add('gold', NumberType::class, [
                'label' => 'form.item.gold',
            ])
            ->add('gems', NumberType::class, [
                'label' => 'form.item.gems',
            ])
            ->add('weight', NumberType::class, [
                'label' => 'form.item.weight',
            ])
            ->add('droppable', CheckboxType::class, [
                'required' => false,
                'label'    => 'form.item.droppable',
            ])
            ->add('level', RangeType::class, [
                'label' => 'form.item.level',
                'attr'  => [
                    'min'                   => 1,
                    'max'                   => LotgdSetting::getSetting('maxlevel'),
                    'disable_slider_labels' => false,
                    'step'                  => 1,
                ],
            ])
            ->add('dragonkills', NumberType::class, [
                'label' => 'form.item.dragonkills',
            ])
            ->add('customValue', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'form.item.customValue',
            ])
            ->add('execCustomValue', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'form.item.execCustomValue',
            ])
            ->add('exectext', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'form.item.exectext.label',
                'help'  => 'form.item.exectext.help',
            ])
            ->add('noEffectText', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'form.item.noEffectText',
            ])
            ->add('execValue', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'form.item.execValue',
            ])
            ->add('execrequisites', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'form.item.execrequisites.label',
                'help'  => 'form.item.execrequisites.help',
            ])
            ->add('hide', CheckboxType::class, [
                'required' => false,
                'label'    => 'form.item.hide',
            ])
            ->add('buff', ChoiceType::class, [
                'required'     => false,
                'label'        => 'form.item.buff',
                'choices'      => Doctrine::getRepository('LotgdLocal:ModInventoryBuff')->findAll(),
                'choice_value' => 'id',
                'choice_label' => function ($buff)
                {
                    return "{$buff->getName()} ({$buff->getKey()})";
                },
            ])
            ->add('charges', NumberType::class, [
                'label' => 'form.item.charges',
            ])
            ->add('activationHook', BitFieldType::class, [
                'label'    => 'form.item.activationHook.label',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'choices'  => [
                    'form.item.activationHook.option.newday'    => HOOK_NEWDAY,
                    'form.item.activationHook.option.forest'    => HOOK_FOREST,
                    'form.item.activationHook.option.village'   => HOOK_VILLAGE,
                    'form.item.activationHook.option.shades'    => HOOK_SHADES,
                    'form.item.activationHook.option.fightnav'  => HOOK_FIGHTNAV,
                    'form.item.activationHook.option.train'     => HOOK_TRAIN,
                    'form.item.activationHook.option.inventory' => HOOK_INVENTORY,
                ],
            ])
            ->add('find_rarity', ChoiceType::class, [
                'required'   => false,
                'empty_data' => '',
                'label'      => 'form.item.find_rarity.label',
                'choices'    => [
                    'form.item.find_rarity.option.common'   => 'common',
                    'form.item.find_rarity.option.uncommon' => 'uncommon',
                    'form.item.find_rarity.option.rare'     => 'rare',
                    'form.item.find_rarity.option.legend'   => 'legend',
                ],
                // enum,',
            ])
            ->add('findChance', RangeType::class, [
                'label' => 'form.item.findChance',
                'attr'  => [
                    'min'                   => 0,
                    'max'                   => 100,
                    'disable_slider_labels' => false,
                    'step'                  => 1,
                ],
            ])
            ->add('looseChance', RangeType::class, [
                'label' => 'form.item.looseChance',
                'attr'  => [
                    'min'                   => 0,
                    'max'                   => 100,
                    'disable_slider_labels' => false,
                    'step'                  => 1,
                ],
            ])
            ->add('dkLooseChance', RangeType::class, [
                'label' => 'form.item.dkLooseChance',
                'attr'  => [
                    'min'                   => 0,
                    'max'                   => 100,
                    'disable_slider_labels' => false,
                    'step'                  => 1,
                ],
            ])
            ->add('sellable', CheckboxType::class, [
                'required' => false,
                'label'    => 'form.item.sellable',
            ])
            ->add('buyable', CheckboxType::class, [
                'required' => false,
                'label'    => 'form.item.buyable',
            ])
            ->add('uniqueForServer', CheckboxType::class, [
                'required' => false,
                'label'    => 'form.item.uniqueForServer',
            ])
            ->add('uniqueForPlayer', CheckboxType::class, [
                'required' => false,
                'label'    => 'form.item.uniqueForPlayer',
            ])
            ->add('equippable', CheckboxType::class, [
                'required' => false,
                'label'    => 'form.item.equippable',
            ])
            ->add('equipWhere', ChoiceType::class, [
                'required' => false,
                'label'    => 'form.item.equipWhere',
                'choices'  => [
                    'equipment.none'      => 'none',
                    'equipment.head'      => 'head',
                    'equipment.armor'     => 'armor',
                    'equipment.mainhand'  => 'mainhand',
                    'equipment.belt'      => 'belt',
                    'equipment.offhand'   => 'offhand',
                    'equipment.righthand' => 'righthand',
                    'equipment.trausers'  => 'trausers',
                    'equipment.lefthand'  => 'lefthand',
                    'equipment.rightring' => 'rightring',
                    'equipment.feet'      => 'feet',
                    'equipment.leftring'  => 'leftring',
                ],
            ])

            ->add('save', SubmitType::class, ['label' => 'form.item.button.save'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => ModInventoryItem::class,
            'translation_domain' => 'module_inventory',
        ]);
    }
}
