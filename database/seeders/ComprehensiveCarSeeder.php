<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\CarModel;
use Illuminate\Support\Facades\DB;

class ComprehensiveCarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚗 بدء إضافة البراندات والموديلات الشاملة...');
        
        // قراءة ملف JSON
        $jsonPath = database_path('data/comprehensive_cars.json');
        
        if (!file_exists($jsonPath)) {
            $this->command->error('❌ ملف comprehensive_cars.json غير موجود!');
            return;
        }
        
        $carData = json_decode(file_get_contents($jsonPath), true);
        
        if (!$carData || !isset($carData['brands'])) {
            $this->command->error('❌ ملف JSON غير صحيح!');
            return;
        }
        
        $totalBrands = count($carData['brands']);
        $totalModels = 0;
        $newBrands = 0;
        $newModels = 0;
        
        $this->command->info("📊 إجمالي البراندات في الملف: {$totalBrands}");
        
        // إنشاء البراندات والموديلات
        foreach ($carData['brands'] as $index => $brandData) {
            $brandNumber = $index + 1;
            $this->command->info("🏭 [{$brandNumber}/{$totalBrands}] معالجة براند: {$brandData['name']}");
            
            try {
                // البحث عن البراند الموجود أو إنشاء جديد
                $brand = Brand::firstOrCreate(
                    ['name' => $brandData['name']],
                    ['name' => $brandData['name']]
                );
                
                if ($brand->wasRecentlyCreated) {
                    $newBrands++;
                    $this->command->info("   ✅ تم إنشاء براند جديد: {$brandData['name']}");
                } else {
                    $this->command->info("   ℹ️ البراند موجود مسبقاً: {$brandData['name']}");
                }
                
                if (isset($brandData['models']) && is_array($brandData['models'])) {
                    $modelsCount = count($brandData['models']);
                    $brandNewModels = 0;
                    
                    $this->command->info("   📝 معالجة {$modelsCount} موديل...");
                    
                    foreach ($brandData['models'] as $modelName) {
                        // البحث عن الموديل الموجود أو إنشاء جديد
                        $model = CarModel::firstOrCreate(
                            [
                                'name' => $modelName,
                                'brand_id' => $brand->id
                            ],
                            [
                                'name' => $modelName,
                                'brand_id' => $brand->id
                            ]
                        );
                        
                        if ($model->wasRecentlyCreated) {
                            $brandNewModels++;
                            $newModels++;
                        }
                    }
                    
                    $totalModels += $modelsCount;
                    
                    if ($brandNewModels > 0) {
                        $this->command->info("   ✅ تم إضافة {$brandNewModels} موديل جديد");
                    } else {
                        $this->command->info("   ℹ️ جميع الموديلات موجودة مسبقاً");
                    }
                }
                
            } catch (\Exception $e) {
                $this->command->error("   ❌ خطأ في معالجة براند {$brandData['name']}: " . $e->getMessage());
            }
        }
        
        // عرض الإحصائيات النهائية
        $this->command->info('');
        $this->command->info('🎉 تم الانتهاء من معالجة البراندات والموديلات!');
        $this->command->info("📊 الإحصائيات النهائية:");
        $this->command->info("   🏭 إجمالي البراندات في النظام: " . Brand::count());
        $this->command->info("   📝 إجمالي الموديلات في النظام: " . CarModel::count());
        $this->command->info("   🆕 البراندات الجديدة: " . $newBrands);
        $this->command->info("   🆕 الموديلات الجديدة: " . $newModels);
        $this->command->info('');
        
        // عرض عينة من البيانات
        $this->command->info('🔍 عينة من البيانات في النظام:');
        $sampleBrands = Brand::with('models')->take(3)->get();
        
        foreach ($sampleBrands as $brand) {
            $this->command->info("   🏭 {$brand->name}: " . $brand->models->count() . " موديل");
            $sampleModels = $brand->models->take(3)->pluck('name')->toArray();
            $this->command->info("      📝 " . implode(', ', $sampleModels));
        }
        
        $this->command->info('');
        $this->command->info('💡 تم تحديث النظام مع الحفاظ على البيانات الموجودة!');
        $this->command->info('🚗 يمكنك الآن استخدام النظام مع قاعدة بيانات شاملة للسيارات!');
    }
}
