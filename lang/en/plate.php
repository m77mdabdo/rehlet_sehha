<?php

declare(strict_types=1);

/*
| Build your plate.
|
| NOT A SINGLE NUMBER IN THIS FILE, AND THERE MUST NEVER BE ONE.
|
| No calories, no grams, no portions, no scores, no targets, no percentages.
| Every string here talks about PROPORTION — what the plate is mostly made of,
| and what it is missing.
|
| The reason is clinical, not stylistic. Numeric feedback teaches people to
| measure food, and measuring food is the habit this clinic exists to undo. It
| is also actively harmful to anyone with a disordered relationship to eating:
| a number attached to a food is the mechanism of the disorder, not neutral
| information. A tool on a nutrition clinic's homepage must not be a calorie
| counter in disguise.
|
| PlateFeedbackHasNoNumbersTest fails the build if a digit appears in any
| feedback string. That test is not to be relaxed.
*/

return [
    /*
    | The lead deliberately does not use the word "calorie", even to disavow
    | it. HomePageTest forbids the term anywhere in the rendered page, and that
    | guard is right: naming it — however negatively — still puts it in front
    | of somebody who came here trying not to think about it.
    */
    'eyebrow' => 'Try it yourself',
    'title' => 'Build your plate',
    'lead' => 'Tap the foods you usually eat and see the shape of your plate. No numbers, no scales — the whole idea is proportion.',

    'groups' => [
        'vegetable' => 'Vegetables',
        'protein' => 'Protein',
        'starch' => 'Starch',
        'fat' => 'Fats',
        'fruit' => 'Fruit',
        'dairy' => 'Dairy',
    ],

    'plate_label' => 'Your plate',
    'plate_empty' => 'The plate is empty',
    'reset' => 'Start again',
    'add' => 'Add :food',
    'remove' => 'Remove :food',
    'chosen' => 'On the plate',

    /*
    | One string per state. The tool picks the FIRST that applies, so the order
    | in the JavaScript is the priority order: an empty plate, then a plate
    | dominated by one group, then a plate missing something, then balance.
    */
    'feedback' => [
        'empty' => 'Start by adding anything you would eat in an ordinary meal.',
        'mostly_starch' => 'This plate is mostly starch. It needs vegetables and a protein alongside it.',
        'mostly_protein' => 'This plate is mostly protein. Vegetables would balance it.',
        'mostly_fat' => 'Fats are taking up most of the plate. Try adding vegetables and a protein.',
        'mostly_fruit' => 'This plate is all fruit. Lovely as a snack, but as a meal it needs a protein.',
        'mostly_dairy' => 'This plate is mostly dairy. Add some vegetables and something starchy.',
        'mostly_vegetable' => 'Plenty of vegetables, which is good. Add a protein so the meal actually fills you up.',
        'no_vegetable' => 'There are no vegetables here. Vegetables are what give a meal its volume and staying power.',
        'no_protein' => 'There is no protein. Protein is what makes fullness last.',
        'no_starch' => 'There is no starch. Starch is not the enemy — it is your main source of energy.',
        'balanced' => 'This plate is balanced: vegetables, protein and starch together. That is the shape to aim for.',
    ],

    'disclaimer' => 'This tool is for illustration only, and is not a substitute for a plan from a practitioner.',
];
