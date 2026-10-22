<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $demoRestaurant = Restaurant::query()->where('slug', 'restaurant-demo')->first();
        if (! $demoRestaurant) {
            return;
        }

        $catEntrees = Category::query()->where('restaurant_id', $demoRestaurant->id)->where('slug', 'entrees-tapas')->first();
        $catPlats = Category::query()->where('restaurant_id', $demoRestaurant->id)->where('slug', 'plats-chauds-grillades')->first();
        $catBurgers = Category::query()->where('restaurant_id', $demoRestaurant->id)->where('slug', 'burgers-street-food')->first();
        $catBoissons = Category::query()->where('restaurant_id', $demoRestaurant->id)->where('slug', 'boissons-cocktails')->first();
        $catDesserts = Category::query()->where('restaurant_id', $demoRestaurant->id)->where('slug', 'desserts-gourmands')->first();

        $groupCuisson = OptionGroup::query()->where('restaurant_id', $demoRestaurant->id)->where('name', 'Cuisson de la viande')->first();
        $groupSauces = OptionGroup::query()->where('restaurant_id', $demoRestaurant->id)->where('name', 'Sauces d\'accompagnement')->first();
        $groupSupplements = OptionGroup::query()->where('restaurant_id', $demoRestaurant->id)->where('name', 'Suppléments gourmands')->first();

        // 1. Burger Qreatery Signature (Avec variantes et options)
        if ($catBurgers) {
            $burger = Product::query()->updateOrCreate(
                ['restaurant_id' => $demoRestaurant->id, 'slug' => 'burger-qreatery-signature'],
                [
                    'category_id' => $catBurgers->id,
                    'name' => 'Burger Qreatery Signature',
                    'description' => 'Pain brioché artisanal, steak haché façon bouchère, oignons caramélisés, sauce secrète du chef et frites fraîches.',
                    'price_minor' => 650000, // 6 500 FCFA
                    'status' => 'active',
                    'is_available' => true,
                    'preparation_minutes' => 15,
                    'position' => 1,
                ]
            );

            // Variantes (Simple vs Double)
            ProductVariant::query()->updateOrCreate(
                ['product_id' => $burger->id, 'name' => 'Simple Steak (150g)'],
                ['price_delta_minor' => 0, 'is_default' => true, 'is_active' => true, 'position' => 1]
            );
            ProductVariant::query()->updateOrCreate(
                ['product_id' => $burger->id, 'name' => 'Double Steak (300g)'],
                ['price_delta_minor' => 250000, 'is_default' => false, 'is_active' => true, 'position' => 2]
            );

            // Liaison Groupes d'options
            $optGroupIds = array_filter([$groupCuisson?->id, $groupSauces?->id, $groupSupplements?->id]);
            $burger->optionGroups()->sync($optGroupIds);
        }

        // 2. Salade César Croustillante
        if ($catEntrees) {
            Product::query()->updateOrCreate(
                ['restaurant_id' => $demoRestaurant->id, 'slug' => 'salade-cesar-croustillante'],
                [
                    'category_id' => $catEntrees->id,
                    'name' => 'Salade César Croustillante',
                    'description' => 'Laitue romaine croquante, aiguillettes de poulet panées dorées, copeaux de parmesan, croûtons à l\'ail et sauce césar maison.',
                    'price_minor' => 400000, // 4 000 FCFA
                    'status' => 'active',
                    'is_available' => true,
                    'preparation_minutes' => 10,
                    'position' => 1,
                ]
            );

            // Nems aux crevettes
            Product::query()->updateOrCreate(
                ['restaurant_id' => $demoRestaurant->id, 'slug' => 'nems-crevettes-fraiches'],
                [
                    'category_id' => $catEntrees->id,
                    'name' => 'Nems aux Crevettes Fraîches (x4)',
                    'description' => 'Quatre nems croustillants faits maison avec farce gourmande aux crevettes fraîches et menthe.',
                    'price_minor' => 450000, // 4 500 FCFA
                    'status' => 'active',
                    'is_available' => true,
                    'preparation_minutes' => 12,
                    'position' => 2,
                ]
            );
        }

        // 3. Plats Chauds & Grillades
        if ($catPlats) {
            // Côte de boeuf
            $coteBoeuf = Product::query()->updateOrCreate(
                ['restaurant_id' => $demoRestaurant->id, 'slug' => 'cote-de-boeuf-braisee'],
                [
                    'category_id' => $catPlats->id,
                    'name' => 'Côte de Bœuf Braisée (400g)',
                    'description' => 'Pièce de choix grillée au feu de bois, servie avec frites de patates douces ou alloco et légumes de saison.',
                    'price_minor' => 1200000, // 12 000 FCFA
                    'status' => 'active',
                    'is_available' => true,
                    'preparation_minutes' => 25,
                    'position' => 1,
                ]
            );
            if ($groupCuisson && $groupSauces) {
                $coteBoeuf->optionGroups()->sync([$groupCuisson->id, $groupSauces->id]);
            }

            // Filet de Mérou braisé
            Product::query()->updateOrCreate(
                ['restaurant_id' => $demoRestaurant->id, 'slug' => 'filet-merou-braise'],
                [
                    'category_id' => $catPlats->id,
                    'name' => 'Filet de Mérou Braisé & Alloco',
                    'description' => 'Pavé de mérou frais de la côte, assaisonné aux aromates locaux et servi avec alloco croustillant.',
                    'price_minor' => 850000, // 8 500 FCFA
                    'status' => 'active',
                    'is_available' => true,
                    'preparation_minutes' => 20,
                    'position' => 2,
                ]
            );

            // Poulet Kedjenou
            Product::query()->updateOrCreate(
                ['restaurant_id' => $demoRestaurant->id, 'slug' => 'poulet-kedjenou-traditionnel'],
                [
                    'category_id' => $catPlats->id,
                    'name' => 'Poulet Kedjenou Traditionnel',
                    'description' => 'Poulet fermier mijoté à l\'étouffée dans un canari en terre cuite avec aubergines et piments doux.',
                    'price_minor' => 750000, // 7 500 FCFA
                    'status' => 'active',
                    'is_available' => true,
                    'preparation_minutes' => 20,
                    'position' => 3,
                ]
            );
        }

        // 4. Boissons & Cocktails
        if ($catBoissons) {
            Product::query()->updateOrCreate(
                ['restaurant_id' => $demoRestaurant->id, 'slug' => 'mojito-passion-frais'],
                [
                    'category_id' => $catBoissons->id,
                    'name' => 'Mojito Passion Frais',
                    'description' => 'Cocktail rafraîchissant avec menthe fraîche, purée de fruit de la passion et citron vert.',
                    'price_minor' => 350000, // 3 500 FCFA
                    'status' => 'active',
                    'is_available' => true,
                    'preparation_minutes' => 5,
                    'position' => 1,
                ]
            );

            Product::query()->updateOrCreate(
                ['restaurant_id' => $demoRestaurant->id, 'slug' => 'bissap-frais-artisanal'],
                [
                    'category_id' => $catBoissons->id,
                    'name' => 'Bissap Frais Artisanal (50cl)',
                    'description' => 'Infusion de fleurs d\'hibiscus rouge préparée chaque matin avec menthe et vanille.',
                    'price_minor' => 150000, // 1 500 FCFA
                    'status' => 'active',
                    'is_available' => true,
                    'preparation_minutes' => 2,
                    'position' => 2,
                ]
            );
        }

        // 5. Desserts Gourmands
        if ($catDesserts) {
            Product::query()->updateOrCreate(
                ['restaurant_id' => $demoRestaurant->id, 'slug' => 'fondant-chocolat-glace-vanille'],
                [
                    'category_id' => $catDesserts->id,
                    'name' => 'Fondant au Chocolat Noir & Glace Vanille',
                    'description' => 'Gâteau au cœur coulant au chocolat noir 70%, accompagné d\'une quenelle de glace vanille bourbon.',
                    'price_minor' => 350000, // 3 500 FCFA
                    'status' => 'active',
                    'is_available' => true,
                    'preparation_minutes' => 10,
                    'position' => 1,
                ]
            );
        }
    }
}
