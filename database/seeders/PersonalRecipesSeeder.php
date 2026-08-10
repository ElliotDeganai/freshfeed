<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostIngredient;
use App\Models\PostStep;
use App\Models\User;
use Illuminate\Database\Seeder;

class PersonalRecipesSeeder extends Seeder
{
    /**
     * ⚠️ Adapte cette adresse à ton compte réel avant de lancer le seeder —
     * c'est le propriétaire auquel les 5 recettes seront rattachées.
     */
    private const OWNER_EMAIL = 'edeganai@gmail.com';

    public function run(): void
    {
        $user = User::where('email', self::OWNER_EMAIL)->first()
            ?? User::role('admin')->first();

        if (! $user) {
            $this->command->error(
                "Aucun utilisateur trouvé (ni " . self::OWNER_EMAIL . ", ni admin de repli). "
                . "Modifie OWNER_EMAIL dans PersonalRecipesSeeder.php avant de relancer."
            );
            return;
        }

        foreach ($this->recipes() as $recipe) {
            $post = Post::updateOrCreate(
                ['title' => $recipe['title'], 'user_id' => $user->id],
                [
                    'content' => $recipe['content'] ?? null,
                    'calories' => $recipe['calories'],
                    'calories_unit' => 'g',
                    'calories_is_auto' => false, // valeurs calculées à la main, à ne pas écraser
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );

            // Idempotent : on repart de zéro sur les ingrédients/étapes à chaque
            // exécution, plutôt que d'essayer de "differ" intelligemment.
            $post->ingredients()->delete();
            foreach ($recipe['ingredients'] as $i => $ingredient) {
                PostIngredient::create([
                    'post_id' => $post->id,
                    'name' => $ingredient[2],
                    'amount' => $ingredient[0],
                    'unit' => $ingredient[1],
                    'order' => $i,
                ]);
            }

            $post->steps()->delete();
            foreach ($recipe['steps'] ?? [] as $i => $instruction) {
                PostStep::create([
                    'post_id' => $post->id,
                    'instruction' => $instruction,
                    'order' => $i,
                ]);
            }

            // Rattache une catégorie seulement si elle existe déjà chez toi —
            // le seeder ne doit jamais planter si tes catégories ont un nom différent.
            if (! empty($recipe['category'])) {
                $category = Category::where('name', $recipe['category'])->first();
                if ($category) {
                    $post->categories()->syncWithoutDetaching([$category->id]);
                }
            }
        }

        $this->command->info(count($this->recipes()) . ' recettes personnelles restaurées pour ' . $user->name . '.');
    }

    /**
     * @return array<int, array{
     *     title: string, calories: int, category?: string, content?: string,
     *     ingredients: array<int, array{0: string, 1: string, 2: string}>,
     *     steps?: array<int, string>
     * }>
     */
    private function recipes(): array
    {
        return [
            [
                'title' => 'Marbré',
                'calories' => 165,
                'category' => 'Dessert',
                'content' => "Le marbré de nos souvenirs d'enfance, version light 🍫🍦 Sucré à la stevia "
                    . "et au miel, moelleux grâce à la compote de pommes — et oui, sans une miette de "
                    . "beurre. Le genre de gâteau qui disparaît en un après-midi.",
                'ingredients' => [
                    ['240', 'g', 'farine'],
                    ['1', 'sachet', 'levure'],
                    ['6', '', 'œufs'],
                    ['1', '', 'stevia (au goût)'],
                    ['4', 'cs', 'arôme vanille'],
                    ['2', 'sachet', 'sucre vanillé'],
                    ['1', 'cs', 'miel'],
                    ['50', 'g', 'cacao non sucré'],
                    ['450', 'g', 'compote sans sucre ajouté'],
                ],
                'steps' => [
                    'Préchauffer le four à 180°C et chemiser un moule à cake.',
                    'Fouetter les œufs avec la stevia, le sucre vanillé, le miel et l\'arôme vanille jusqu\'à ce que le mélange blanchisse légèrement.',
                    'Incorporer la compote de pommes, puis la farine tamisée avec la levure, jusqu\'à obtenir une pâte lisse.',
                    'Prélever environ un tiers de la pâte et y mélanger le cacao non sucré (ajouter une cuillère d\'eau ou de lait d\'amande si la pâte cacaotée est trop épaisse).',
                    'Verser la pâte claire dans le moule, ajouter la pâte au cacao par-dessus, puis marbrer à l\'aide d\'un couteau en zigzag.',
                    'Enfourner 40 à 45 minutes — la lame d\'un couteau plantée au centre doit ressortir sèche.',
                    'Laisser tiédir avant de démouler.',
                ],
            ],

            [
                'title' => 'Brownie light',
                'calories' => 170,
                'category' => 'Dessert',
                'content' => "Le brownie fondant qu'on n'imagine jamais allégé 🍫 Compote de pommes à la "
                    . "place du beurre, stevia à la place du sucre — et pourtant ce fondant en bouche qui "
                    . "fait tout oublier. Le secret : ne pas trop cuire.\n\n"
                    . "Cuisson : 180°C (chaleur tournante de préférence), moule 20×20 cm.\n"
                    . "— Très fudge / coulant au centre : 18–20 min (le centre doit rester légèrement tremblotant)\n"
                    . "— Fondant classique (recommandé) : 20–22 min (cure-dent avec quelques miettes humides)\n"
                    . "— Plus cuit / dense : 22–24 min (cure-dent presque propre)",
                'ingredients' => [
                    ['50', 'g', 'cacao en poudre non sucré'],
                    ['200', 'g', 'compote de pommes sans sucre ajouté'],
                    ['2', '', 'œufs (taille M/L)'],
                    ['80', 'g', 'farine de blé (T45-T65)'],
                    ['10', 'g', 'stevia en poudre (équivalent sucre)'],
                    ['1', 'c. à café', 'levure chimique'],
                    ['1', 'pincée', 'sel'],
                    ['1', 'c. à café', 'arôme vanille naturel'],
                ],
                'steps' => [
                    'Préchauffer le four à 180°C, chaleur tournante de préférence.',
                    'Mélanger le cacao, la compote de pommes, les œufs, la farine, la stevia, la levure, le sel et l\'arôme vanille jusqu\'à obtenir une pâte homogène.',
                    'Verser dans un moule 20×20 cm et enfourner 18 à 24 minutes selon la texture voulue (voir la description pour le détail précis par minute).',
                    'Laisser refroidir avant de démouler — le brownie continue de se raffermir en refroidissant.',
                ],
            ],

            [
                'title' => 'Flan',
                'calories' => 71,
                'category' => 'Dessert',
                'content' => "Le flan tout doux, ultra léger en calories 🍮 Parfait pour se faire plaisir "
                    . "sans culpabiliser — texture fondante garantie après un passage tranquille au "
                    . "bain-marie.\n\n"
                    . "Valeur calorique calculée avec de la stévia \"mélangée\" (coupée avec du sucre) — "
                    . "avec de la stévia pure, on descend plutôt à 59 kcal/100g.\n\n"
                    . "Cuisson au bain-marie, 180°C, 45 à 50 minutes. Poids net après cuisson estimé à ~1240g "
                    . "(perte à la cuisson d'environ 12,5% par évaporation).",
                'ingredients' => [
                    ['4', '', 'œufs entiers (~50g pièce)'],
                    ['2', '', 'blancs d\'œufs (~30g pièce)'],
                    ['1', 'l', 'lait d\'amande non sucré'],
                    ['75', 'g', 'stévia'],
                    ['75', 'g', 'maïzena'],
                    ['2', '', 'gousses de vanille'],
                    ['30', 'g', 'sucre vanillé'],
                ],
                'steps' => [
                    'Fouetter les œufs entiers et les blancs d\'œufs avec la stévia et le sucre vanillé.',
                    'Délayer la maïzena dans un peu de lait d\'amande froid, puis ajouter le reste du lait et les graines des gousses de vanille.',
                    'Mélanger le tout, verser dans un moule et cuire au bain-marie à 180°C pendant 45 à 50 minutes.',
                    'Laisser refroidir complètement avant de démouler, puis réserver au frais.',
                ],
            ],

            [
                'title' => 'Cloud pizza fajitas',
                'calories' => 111,
                'category' => 'Rapide',
                'content' => "La pizza qui plane ☁️🌮 Une base ultra légère montée en neige — oui, sans une "
                    . "once de farine — garnie généreusement façon fajitas : poulet, feta, scamorza "
                    . "fondante. Bluffant question texture, addictif question goût.",
                'ingredients' => [
                    ['5', '', 'blancs d\'œufs'],
                    ['15', 'g', 'maïzena'],
                    ['400', 'g', 'poulet fajitas (~50 kcal/100g)'],
                    ['150', 'g', 'feta'],
                    ['50', 'g', 'scamorza (~270 kcal/100g)'],
                ],
                'steps' => [
                    'Préchauffer le four à 150-160°C. Recouvrir une plaque de papier cuisson.',
                    'Monter les blancs d\'œufs en neige bien ferme, puis incorporer délicatement la maïzena tamisée sans les casser.',
                    'Étaler la préparation sur la plaque en un disque régulier d\'environ 1 à 1,5 cm d\'épaisseur.',
                    'Enfourner 20 à 25 minutes, jusqu\'à ce que la base soit ferme et légèrement dorée.',
                    'Sortir la base et monter le four à 200°C.',
                    'Garnir avec le poulet fajitas, la feta émiettée et la scamorza râpée.',
                    'Remettre au four 8 à 10 minutes, jusqu\'à ce que le fromage soit fondu et légèrement gratiné.',
                ],
            ],

            [
                'title' => 'Pancakes',
                'calories' => 140,
                'category' => 'Petit-déjeuner',
                'content' => "Le brunch du dimanche, version protéinée 🥞 Moelleux, légers, faits pour "
                    . "empiler sans compter. Le genre de pancakes qui donnent tout de suite envie de se "
                    . "resservir.\n\n"
                    . "Note : la quantité de lait d'amande d'origine (200cl, soit 2 litres) semble "
                    . "disproportionnée par rapport à 400g de farine — j'ai ramené à 200ml, plus cohérent "
                    . "pour une pâte à pancakes. Ajuste selon la texture obtenue.",
                'ingredients' => [
                    ['8', '', 'blancs d\'œuf'],
                    ['400', 'g', 'farine'],
                    ['1', '', 'stevia (au goût)'],
                    ['1.5', 'sachet', 'levure'],
                    ['1', 'pincée', 'sel'],
                    ['1', '', 'arôme de vanille'],
                    ['1', '', 'gousse de vanille'],
                    ['200', 'ml', 'lait d\'amande non sucré'],
                    ['1', 'cs', 'miel'],
                ],
                'steps' => [
                    'Fouetter les blancs d\'œufs avec le miel, l\'arôme de vanille et les graines de la gousse de vanille.',
                    'Ajouter le lait d\'amande petit à petit en fouettant pour éviter les grumeaux.',
                    'Incorporer la farine, la levure, la stevia et le sel, mélanger jusqu\'à obtenir une pâte lisse.',
                    'Laisser reposer la pâte 10 minutes à température ambiante.',
                    'Cuire à la poêle chaude légèrement huilée, environ 2 minutes de chaque côté — retourner quand des bulles apparaissent en surface.',
                ],
            ],

            [
                'title' => 'Banana bread',
                'calories' => 185,
                'category' => 'Petit-déjeuner',
                'content' => "Le banana bread healthy qui recycle tes bananes trop mûres 🍌 Sans beurre, "
                    . "sans sucre ajouté — juste des flocons d'avoine, de la compote et un soupçon de miel "
                    . "pour la gourmandise. Moelleux et parfumé à la vanille.",
                'ingredients' => [
                    ['2', '', 'œufs'],
                    ['2', '', 'bananes mûres'],
                    ['150', 'g', 'flocons d\'avoine'],
                    ['2', '', 'gousses de vanille'],
                    ['1', 'pincée', 'sel'],
                    ['100', 'g', 'yaourt nature (96 kcal/100g)'],
                    ['50', 'g', 'miel'],
                    ['0.5', 'sachet', 'levure'],
                    ['120', 'g', 'stevia pure'],
                    ['60', 'g', 'compote de pomme sans sucre ajouté'],
                ],
                'steps' => [
                    'Préchauffer le four à 180°C et chemiser un moule à cake.',
                    'Écraser les bananes mûres à la fourchette jusqu\'à obtenir une purée.',
                    'Mélanger les œufs, le yaourt, le miel, la stevia et les graines des gousses de vanille.',
                    'Incorporer la purée de bananes et la compote de pommes.',
                    'Ajouter les flocons d\'avoine, la levure et le sel, mélanger jusqu\'à homogénéité.',
                    'Verser dans le moule et enfourner 40 à 45 minutes — un couteau planté au centre doit ressortir sec.',
                    'Laisser refroidir avant de démouler.',
                ],
            ],

            [
                'title' => 'Bolognaise carottes',
                'calories' => 58,
                'category' => 'Plat',
                'content' => "La bolognaise du quotidien, sans une goutte d'huile 🥕 Des légumes fondants, "
                    . "une viande bien dorée et une sauce qui mijote tranquillement — la base parfaite à "
                    . "décliner en pâtes, gratin ou farce, sans jamais culpabiliser.",
                'ingredients' => [
                    ['1000', 'g', 'steak haché de bœuf 5% matière grasse'],
                    ['500', 'g', 'oignons'],
                    ['400', 'g', 'carottes'],
                    ['400', 'g', 'céleri'],
                    ['1200', 'g', 'tomates (~23 kcal/100g)'],
                    ['1', '', 'concentré de tomate (au goût)'],
                    ['1', '', 'cumin (au goût)'],
                    ['1', '', 'paprika (au goût)'],
                    ['1', '', 'ail haché (au goût)'],
                    ['1', '', 'Kub Or (au goût)'],
                ],
                'steps' => [
                    'Faire revenir les oignons, carottes et céleri avec un petit filet d\'eau, en mélangeant à intervalle régulier, jusqu\'à ce qu\'ils soient tendres.',
                    'Ajouter le concentré de tomate et laisser cuire quelques instants.',
                    'Ajouter la viande hachée et laisser bien colorer.',
                    'Une fois la viande colorée, ajouter les tomates, l\'ail haché, le cumin, le paprika et le Kub Or.',
                    'Laisser cuire à feu moyen jusqu\'à réduction satisfaisante de la sauce.',
                ],
            ],

            [
                'title' => 'Base Poulet poivron fajitas',
                'calories' => 50,
                'category' => 'Plat',
                'content' => "La base fajitas à avoir toujours sous la main 🌶️ Poulet, poivrons et oignons "
                    . "fondants, relevés aux épices — parfaite pour des wraps, un riz sauté ou directement "
                    . "dans une salade.",
                'ingredients' => [
                    ['600', 'g', 'poulet'],
                    ['400', 'g', 'oignons'],
                    ['400', 'g', 'poivron'],
                    ['800', 'g', 'tomate'],
                    ['1', '', 'cumin (au goût)'],
                    ['1', '', 'paprika (au goût)'],
                    ['1', '', 'ail haché (au goût)'],
                    ['1', '', 'Kub Or (au goût)'],
                    ['1', '', 'curcuma (au goût)'],
                ],
                'steps' => [
                    'Faire revenir les poivrons et les oignons.',
                    'Ajouter le poulet et laisser cuire jusqu\'à ce qu\'il soit bien coloré.',
                    'Ajouter la tomate, l\'ail haché, le cumin, le paprika, le curcuma et le Kub Or, puis laisser mijoter jusqu\'à réduction satisfaisante.',
                ],
            ],
        ];
    }
}
