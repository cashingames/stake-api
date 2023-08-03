<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{

    public function saveCategoryIcon(Request $request)
    {
        $request->validate([
            'categoryName' => 'required|string|max:200',
            'icon'     =>  'required|image|mimes:jpeg,png,jpg,gif,base64|max:1024'//1mb max
        ]);

        $category = Category::where('name', $request->categoryName)->first();

        if ($request->hasFile('icon')) {
            $image = $request->file('icon');
            $name = str_replace(' ', '_', $request->categoryName) . "." . $image->guessExtension();
            $destinationPath = public_path('icons');
            $category->icon = 'icons/' . $name;
            $image->move($destinationPath, $name);
            
            $category->save();

            return $this->sendResponse("Icon saved", "Icon saved");
        }
    }

}
