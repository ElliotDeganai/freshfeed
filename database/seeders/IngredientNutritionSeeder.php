<?php

namespace Database\Seeders;

use App\Models\IngredientNutrition;
use Illuminate\Database\Seeder;

class IngredientNutritionSeeder extends Seeder
{
    /**
     * Base de référence locale pour l'estimation automatique des calories.
     * Valeurs approximatives (kcal / 100g ou 100ml), sources type USDA/CIQUAL,
     * suffisantes pour une ESTIMATION — pas une valeur nutritionnelle certifiée.
     *
     * Convention importante : les valeurs correspondent au poids tel qu'il est
     * généralement indiqué dans une liste d'ingrédients AVANT cuisson (riz cru,
     * pâtes crues, semoule sèche...), car c'est ainsi que les quantités sont
     * habituellement saisies. Exception : les légumineuses (pois chiches,
     * haricots, lentilles) utilisent des valeurs cuites/en conserve, convention
     * la plus fréquente en cuisine courante ("1 boîte de pois chiches").
     *
     * Limite connue : l'eau absorbée pendant la cuisson (riz, pâtes...) change le
     * poids final du plat sans changer les calories totales — le ratio kcal/100g
     * calculé ici représente donc les calories rapportées au poids des ingrédients
     * CRUS additionnés, pas au poids du plat cuit fini. C'est une approximation
     * assumée, pas une valeur nutritionnelle de précision.
     *
     * Un 5ème élément optionnel (standard_unit_weight, en grammes) est fourni pour
     * les aliments couramment comptés à l'unité plutôt que pesés (ex: "2 œufs",
     * "1 avocat") — il sert de repli quand aucune unité de poids/volume n'est
     * précisée dans la recette.
     */
    public function run(): void
    {
        foreach ($this->ingredients() as $row) {
            [$name, $kcal, $kind, $aliases] = $row;

            IngredientNutrition::updateOrCreate(
                ['name' => $name],
                [
                    'kcal_per_100' => $kcal,
                    'kind' => $kind,
                    'aliases' => $aliases,
                    'standard_unit_weight' => $row[4] ?? null,
                    'source' => 'seed',
                ]
            );
        }

        $this->command->info(count($this->ingredients()) . ' ingrédients de référence chargés.');
    }

    /**
     * @return array<int, array{0:string,1:int,2:string,3:array<string>}>
     */
    private function ingredients(): array
    {
        return [
            // Viandes & volailles
            ['poulet', 165, 'solid', ['blanc de poulet', 'filet de poulet', 'escalope de poulet']],
            ['boeuf', 250, 'solid', ['bœuf', 'viande de boeuf', 'steak']],
            ['boeuf hache', 254, 'solid', ['bœuf haché', 'viande hachée', 'steak haché']],
            ['porc', 242, 'solid', ['viande de porc', 'filet de porc']],
            ['jambon', 145, 'solid', ['jambon blanc', 'jambon cuit']],
            ['bacon', 541, 'solid', ['lardons', 'poitrine fumee']],
            ['dinde', 135, 'solid', ['blanc de dinde', 'escalope de dinde']],
            ['canard', 337, 'solid', []],
            ['agneau', 294, 'solid', ['gigot d\'agneau']],
            ['chorizo', 455, 'solid', []],
            ['guanciale', 550, 'solid', ['pancetta']],
            ['saucisse', 300, 'solid', ['saucisses']],

            // Poissons & fruits de mer
            ['saumon', 208, 'solid', ['pave de saumon', 'filet de saumon']],
            ['thon', 144, 'solid', ['thon frais', 'thon en boite']],
            ['cabillaud', 82, 'solid', ['morue', 'filet de cabillaud']],
            ['crevette', 99, 'solid', ['crevettes']],
            ['saumon fume', 117, 'solid', []],
            ['sardine', 208, 'solid', ['sardines']],

            // Oeufs & laitages
            ['oeuf', 155, 'solid', ['œuf', 'oeufs', 'œufs', 'oeuf entier'], 50],
            ['lait', 42, 'liquid', ['lait entier', 'lait demi-ecreme']],
            ['lait damande', 17, 'liquid', ['lait d\'amande']],
            ['lait de coco', 230, 'liquid', []],
            ['creme fraiche', 292, 'liquid', ['crème fraîche', 'creme liquide', 'crème liquide']],
            ['yaourt', 61, 'solid', ['yaourt nature']],
            ['yaourt grec', 97, 'solid', []],
            ['fromage', 350, 'solid', ['fromage rape', 'fromage râpé']],
            ['parmesan', 431, 'solid', []],
            ['mozzarella', 280, 'solid', []],
            ['feta', 264, 'solid', []],
            ['beurre', 717, 'solid', []],
            ['tofu', 76, 'solid', ['tofu soyeux', 'tofu ferme']],

            // Céréales, féculents, pains
            ['riz', 365, 'solid', ['riz blanc', 'riz cru']],
            ['riz complet', 362, 'solid', []],
            ['riz arborio', 349, 'solid', []],
            ['riz a sushi', 358, 'solid', ['riz à sushi']],
            ['pates', 371, 'solid', ['pâtes', 'spaghetti', 'pates crues']],
            ['quinoa', 368, 'solid', []],
            ['farine', 364, 'solid', ['farine de ble', 'farine de blé']],
            ['farine de sarrasin', 335, 'solid', []],
            ['farine de riz', 366, 'solid', []],
            ['pain', 265, 'solid', []],
            ['tortilla', 218, 'solid', ['tortillas']],
            ['flocons davoine', 389, 'solid', ["flocons d'avoine", 'avoine']],
            ['pate brisee', 450, 'solid', ['pâte brisée']],
            ['pate feuilletee', 406, 'solid', ['pâte feuilletée']],
            ['couscous', 376, 'solid', []],
            ['polenta', 362, 'solid', ['semoule de mais', 'semoule de maïs']],
            ['granola', 471, 'solid', []],
            ['croutons', 407, 'solid', ['croûtons']],

            // Légumineuses
            ['pois chiche', 164, 'solid', ['pois chiches']],
            ['haricot rouge', 127, 'solid', ['haricots rouges']],
            ['lentille', 116, 'solid', ['lentilles']],
            ['edamame', 121, 'solid', []],
            ['proteines de soja texturees', 335, 'solid', ['protéines de soja texturées', 'soja texture']],

            // Légumes
            ['tomate', 18, 'solid', ['tomates'], 120],
            ['tomates concassees', 24, 'solid', ['tomates concassées']],
            ['concombre', 15, 'solid', [], 300],
            ['carotte', 41, 'solid', ['carottes', 'carottes rapees', 'carottes râpées'], 60],
            ['oignon', 40, 'solid', ['oignons'], 110],
            ['oignon vert', 32, 'solid', ['oignons verts', 'ciboule'], 15],
            ['ail', 149, 'solid', ["gousse d'ail", 'gousses d\'ail'], 5],
            ['poivron', 31, 'solid', ['poivron rouge', 'poivron vert'], 120],
            ['courgette', 17, 'solid', ['courgettes'], 200],
            ['aubergine', 25, 'solid', ['aubergines'], 250],
            ['patate douce', 86, 'solid', [], 200],
            ['pomme de terre', 77, 'solid', ['pommes de terre'], 150],
            ['chou-fleur', 25, 'solid', []],
            ['brocoli', 34, 'solid', []],
            ['chou rouge', 31, 'solid', []],
            ['epinard', 23, 'solid', ['épinard', 'epinards', 'pousses depinards', 'pousses d\'épinards']],
            ['laitue', 15, 'solid', ['laitue romaine', 'salade']],
            ['champignon', 22, 'solid', ['champignons', 'champignons de paris'], 15],
            ['avocat', 160, 'solid', [], 200],
            ['mais', 86, 'solid', ['maïs']],
            ['asperge', 20, 'solid', ['asperges', 'asperges vertes']],
            ['algue wakame', 45, 'solid', []],

            // Fruits
            ['banane', 89, 'solid', ['bananes'], 120],
            ['pomme', 52, 'solid', ['pommes'], 180],
            ['citron', 29, 'solid', [], 100],
            ['orange', 47, 'solid', [], 150],
            ['mangue', 60, 'solid', [], 300],
            ['fruits rouges', 50, 'solid', ['fruits rouges surgeles', 'fruits rouges surgelés']],
            ['ananas', 50, 'solid', [], 900],

            // Matières grasses & huiles
            ['huile dolive', 884, 'liquid', ["huile d'olive"]],
            ['huile de coco', 862, 'liquid', []],
            ['huile de tournesol', 884, 'liquid', []],

            // Sucres, édulcorants, chocolat
            ['sucre', 387, 'solid', ['sucre roux', 'sucre blanc']],
            ['sucre glace', 389, 'solid', []],
            ['stevia', 0, 'solid', []],
            ['miel', 304, 'solid', []],
            ['chocolat noir', 546, 'solid', []],
            ['cacao', 228, 'solid', ['cacao en poudre']],
            ['compote de pommes', 45, 'solid', ['compote', 'compote pomme']],

            // Fruits secs, graines, noix
            ['amande', 579, 'solid', ['amandes']],
            ['noix', 654, 'solid', []],
            ['graines de chia', 486, 'solid', []],
            ['tahini', 595, 'liquid', []],
            ['houmous', 166, 'solid', []],

            // Sauces, condiments, épices
            ['sauce soja', 53, 'liquid', []],
            ['moutarde', 66, 'solid', []],
            ['mayonnaise', 680, 'solid', []],
            ['pate de curry', 100, 'solid', ['pâte de curry']],
            ['bouillon de legumes', 5, 'liquid', ['bouillon de légumes']],
            ['bouillon dashi', 5, 'liquid', []],
            ['pate miso', 199, 'solid', ['pâte miso']],
            ['vin blanc', 82, 'liquid', ['vin blanc sec']],
            ['vinaigre', 18, 'liquid', []],
            ['epices', 0, 'solid', ['épices', 'epices tex-mex']],
            ['sel', 0, 'solid', []],
            ['poivre', 251, 'solid', ['poivre noir']],
            ['paprika', 282, 'solid', ['paprika fume', 'paprika fumé']],
            ['cannelle', 247, 'solid', []],
            ['levure chimique', 53, 'solid', []],

            // Protéines en poudre / compléments
            ['proteine en poudre', 380, 'solid', ['protéine en poudre', 'proteine en poudre vanille']],
            ['aquafaba', 21, 'liquid', ["eau de pois chiches"]],
        ];
    }
}
