<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WeekPlan;
use App\Services\WeekPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadyMealController extends Controller
{
    protected WeekPlanService $weekPlanService;

    public function __construct(WeekPlanService $weekPlanService)
    {
        $this->weekPlanService = $weekPlanService;
    }

    /**
     * Получение списка готовых блюд
     */
    public function index(): JsonResponse
    {
        try {
            $breakfasts = [
                [
                    'id' => '1',
                    'name' => 'Тост из цельного зерна с мягким сыром и помидором кольцами',
                    'type' => 'Завтрак',
                    'calories' => 350,
                    'proteins' => 15,
                    'fats' => 12,
                    'carbs' => 45,
                    'description' => 'Омлет из 2 яиц и 1 белка с зеленью'
                ],
                [
                    'id' => '2',
                    'name' => 'Горсть ягод на выбор и 1/2 груши',
                    'type' => 'Завтрак',
                    'calories' => 250,
                    'proteins' => 8,
                    'fats' => 5,
                    'carbs' => 35,
                    'description' => 'Творог с йогуртом без добавок, гречневая каша с молоком'
                ],
                [
                    'id' => '3',
                    'name' => 'Микс салат из листьев и болгарского перца',
                    'type' => 'Завтрак',
                    'calories' => 280,
                    'proteins' => 10,
                    'fats' => 8,
                    'carbs' => 40,
                    'description' => '2 отварных яйца, тост с малосольной рыбой и мягким творогом'
                ],
                [
                    'id' => '4',
                    'name' => 'Пюре из замороженных ягод с небольшим количеством сахара',
                    'type' => 'Завтрак',
                    'calories' => 220,
                    'proteins' => 6,
                    'fats' => 4,
                    'carbs' => 38,
                    'description' => 'Запеченные творожные сырники'
                ],
                [
                    'id' => '5',
                    'name' => 'Тертое яблоко и морковь с добавлением йогурта',
                    'type' => 'Завтрак',
                    'calories' => 190,
                    'proteins' => 7,
                    'fats' => 3,
                    'carbs' => 32,
                    'description' => 'Рисовый пудинг с молоком или без, белковый омлет'
                ]
            ];

            $mainMeals = [
                [
                    'id' => '6',
                    'name' => 'Микс салат с огурцом и болгарским перцем',
                    'type' => 'Основной прием',
                    'calories' => 450,
                    'proteins' => 25,
                    'fats' => 15,
                    'carbs' => 55,
                    'description' => 'Паста болоньезе'
                ],
                [
                    'id' => '7',
                    'name' => 'Салат из белокочанной капусты и моркови с нерафинированным маслом',
                    'type' => 'Основной прием',
                    'calories' => 550,
                    'proteins' => 30,
                    'fats' => 20,
                    'carbs' => 60,
                    'description' => 'Болгарский перец фаршированный говядиной с рисом'
                ],
                [
                    'id' => '8',
                    'name' => 'Морковь тертая со специями и болгарским перцем',
                    'type' => 'Основной прием',
                    'calories' => 480,
                    'proteins' => 28,
                    'fats' => 18,
                    'carbs' => 52,
                    'description' => 'Треска в итальянских травах на пару, булгур отварной'
                ],
                [
                    'id' => '9',
                    'name' => 'Греческий салат',
                    'type' => 'Основной прием',
                    'calories' => 520,
                    'proteins' => 32,
                    'fats' => 22,
                    'carbs' => 58,
                    'description' => 'Филе индейки тушеное в сметане, картофель отварной в кожуре'
                ],
                [
                    'id' => '10',
                    'name' => 'Свекла с грецким орехом с греческим йогуртом',
                    'type' => 'Основной прием',
                    'calories' => 490,
                    'proteins' => 26,
                    'fats' => 16,
                    'carbs' => 54,
                    'description' => 'Гречка с грибами и куриной котлетой'
                ],
                [
                    'id' => '11',
                    'name' => 'Квашеная капуста с клюквой',
                    'type' => 'Основной прием',
                    'calories' => 460,
                    'proteins' => 24,
                    'fats' => 14,
                    'carbs' => 50,
                    'description' => 'Куриная печень в сливках с Пенне'
                ]
            ];

            return response()->json([
                'data' => [
                    'breakfasts' => $breakfasts,
                    'mainMeals' => $mainMeals
                ],
                'message' => 'Список готовых блюд успешно получен'
            ]);
        } catch (\Exception $e) {
            \Log::error('ReadyMealController error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Ошибка получения данных',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Добавление готового блюда в план питания
     */
    public function addToPlan(Request $request): JsonResponse
    {
        $validator = $request->validate([
            'meal_id' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
            'type' => 'required|string|in:Завтрак,Основной прием'
        ]);

        $user = $request->user();
        $plan = WeekPlan::firstOrCreate(
            ['user_id' => $user->id, 'date' => $validator['date']],
            ['meals' => [], 'workouts' => [], 'progress' => 0]
        );

        // Получаем информацию о блюде из списка готовых блюд
        $mealInfo = $this->getMealInfo($validator['meal_id'], $validator['type']);
        if (!$mealInfo) {
            return response()->json([
                'error' => 'Блюдо не найдено',
                'message' => 'Указанное блюдо не существует в базе готовых блюд'
            ], 404);
        }

        $mealData = [
            'id' => $validator['meal_id'],
            'name' => $mealInfo['name'],
            'type' => $mealInfo['type'],
            'calories' => $mealInfo['calories'],
            'proteins' => $mealInfo['proteins'],
            'fats' => $mealInfo['fats'],
            'carbs' => $mealInfo['carbs'],
            'description' => $mealInfo['description'],
            'completed' => false
        ];

        $plan = $this->weekPlanService->addMeal($plan, $mealData);

        return response()->json([
            'data' => $plan,
            'message' => 'Блюдо успешно добавлено в план питания'
        ]);
    }

    /**
     * Получение информации о готовом блюде
     */
    private function getMealInfo(string $mealId, string $type): ?array
    {
        // В реальном приложении здесь будет запрос к базе данных
        // Сейчас используем статические данные
        $allMeals = [
            // Завтраки
            [
                'id' => '1',
                'name' => 'Тост из цельного зерна с мягким сыром и помидором кольцами',
                'type' => 'Завтрак',
                'calories' => 350,
                'proteins' => 15,
                'fats' => 12,
                'carbs' => 45,
                'description' => 'Омлет из 2 яиц и 1 белка с зеленью'
            ],
            [
                'id' => '2',
                'name' => 'Горсть ягод на выбор и 1/2 груши',
                'type' => 'Завтрак',
                'calories' => 250,
                'proteins' => 8,
                'fats' => 5,
                'carbs' => 35,
                'description' => 'Творог с йогуртом без добавок, гречневая каша с молоком'
            ],
            [
                'id' => '3',
                'name' => 'Микс салат из листьев и болгарского перца',
                'type' => 'Завтрак',
                'calories' => 280,
                'proteins' => 10,
                'fats' => 8,
                'carbs' => 40,
                'description' => '2 отварных яйца, тост с малосольной рыбой и мягким творогом'
            ],
            [
                'id' => '4',
                'name' => 'Пюре из замороженных ягод с небольшим количеством сахара',
                'type' => 'Завтрак',
                'calories' => 220,
                'proteins' => 6,
                'fats' => 4,
                'carbs' => 38,
                'description' => 'Запеченные творожные сырники'
            ],
            [
                'id' => '5',
                'name' => 'Тертое яблоко и морковь с добавлением йогурта',
                'type' => 'Завтрак',
                'calories' => 190,
                'proteins' => 7,
                'fats' => 3,
                'carbs' => 32,
                'description' => 'Рисовый пудинг с молоком или без, белковый омлет'
            ],
            // Основные блюда
            [
                'id' => '6',
                'name' => 'Микс салат с огурцом и болгарским перцем',
                'type' => 'Основной прием',
                'calories' => 450,
                'proteins' => 25,
                'fats' => 15,
                'carbs' => 55,
                'description' => 'Паста болоньезе'
            ],
            [
                'id' => '7',
                'name' => 'Салат из белокочанной капусты и моркови с нерафинированным маслом',
                'type' => 'Основной прием',
                'calories' => 550,
                'proteins' => 30,
                'fats' => 20,
                'carbs' => 60,
                'description' => 'Болгарский перец фаршированный говядиной с рисом'
            ],
            [
                'id' => '8',
                'name' => 'Морковь тертая со специями и болгарским перцем',
                'type' => 'Основной прием',
                'calories' => 480,
                'proteins' => 28,
                'fats' => 18,
                'carbs' => 52,
                'description' => 'Треска в итальянских травах на пару, булгур отварной'
            ],
            [
                'id' => '9',
                'name' => 'Греческий салат',
                'type' => 'Основной прием',
                'calories' => 520,
                'proteins' => 32,
                'fats' => 22,
                'carbs' => 58,
                'description' => 'Филе индейки тушеное в сметане, картофель отварной в кожуре'
            ],
            [
                'id' => '10',
                'name' => 'Свекла с грецким орехом с греческим йогуртом',
                'type' => 'Основной прием',
                'calories' => 490,
                'proteins' => 26,
                'fats' => 16,
                'carbs' => 54,
                'description' => 'Гречка с грибами и куриной котлетой'
            ],
            [
                'id' => '11',
                'name' => 'Квашеная капуста с клюквой',
                'type' => 'Основной прием',
                'calories' => 460,
                'proteins' => 24,
                'fats' => 14,
                'carbs' => 50,
                'description' => 'Куриная печень в сливках с Пенне'
            ]
        ];

        foreach ($allMeals as $meal) {
            if ($meal['id'] === $mealId && $meal['type'] === $type) {
                return $meal;
            }
        }

        return null;
    }
} 