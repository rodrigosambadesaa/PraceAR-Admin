export const UNITY_TYPE = {
    default: "default",
    handicraft: "handicraft",
    butcher: "butcher",
    delicatessen: "delicatessen",
    "fish-seafood": "fish-seafood",
    "cod-frozen": "cod-frozen",
    "poultry-eggs": "poultry-eggs",
    "vegetables-organics": "vegetables-organics",
    "bread-sweets": "bread-sweets",
    flowers: "flowers",
    restaurants: "restaurants",
} as const;

export type UnityType = typeof UNITY_TYPE[keyof typeof UNITY_TYPE];
