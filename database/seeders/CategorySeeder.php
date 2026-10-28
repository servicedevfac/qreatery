<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $reposRestaurant = Restaurant::query()->where('slug', 'restaurant-repos')->first();
        if (! $reposRestaurant) {
            return;
        }

        $categories = [
            [
                'name' => 'Entrées & Tapas',
                'slug' => 'entrees-tapas',
                'description' => 'Mises en bouche fraîches et tapas à partager pour bien commencer.',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Plats Chauds & Grillades',
                'slug' => 'plats-chauds-grillades',
                'description' => 'Nos viandes grillées au feu de bois et spécialités mijotées maison.',
                'position' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Burgers & Street Food',
                'slug' => 'burgers-street-food',
                'description' => 'Burgers gourmets, pains briochés artisanaux et frites croustillantes.',
                'position' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Boissons & Cocktails',
                'slug' => 'boissons-cocktails',
                'description' => 'Jus naturels de saison, cocktails signatures et rafraîchissements.',
                'position' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Desserts Gourmands',
                'slug' => 'desserts-gourmands',
                'description' => 'Douceurs sucrées et glaces artisanales pour terminer sur une note parfaite.',
                'position' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            Category::query()->updateOrCreate(
                ['restaurant_id' => $reposRestaurant->id, 'slug' => $cat['slug']],
                $cat
            );
        }
    }
}
