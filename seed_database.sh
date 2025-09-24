#!/bin/bash

echo "🎨 MuralFinder Database Seeding Script"
echo "======================================"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

echo "🔄 Running database migrations..."
php artisan migrate:fresh

if [ $? -ne 0 ]; then
    echo "❌ Migration failed. Please check your database configuration."
    exit 1
fi

echo "🌱 Seeding database with comprehensive test data..."
php artisan db:seed

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Database seeding completed successfully!"
    echo ""
    echo "📊 Test Data Created:"
    echo "   👤 Users: 1 Admin + 4 Artists + 10 Art Lovers"
    echo "   📂 Categories: 8 Art Categories"
    echo "   🧱 Walls: 8 Famous Street Art Locations"
    echo "   🎨 Artworks: 50 Sample Artworks"
    echo "   📝 Posts: 10 Blog Posts"
    echo "   🛍️ Products: 8 Shop Products"
    echo "   💬 Interactions: Likes & Comments"
    echo ""
    echo "🔑 Test Login Credentials:"
    echo "   Admin: admin@muralfinder.com / password"
    echo "   Artist: artist1@test.com / password"
    echo "   Art Lover: artlover1@test.com / password"
    echo ""
    echo "🚀 Your MuralFinder app is ready for testing!"
else
    echo "❌ Seeding failed. Please check the error messages above."
    exit 1
fi
