<?php

namespace App\ZapatoFeroz\Syschronize\Shopify\Etl\Transformers;

use Carbon\CarbonImmutable;
use App\Zeus\Helpers\ImageHelper;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Storage;
use App\ZapatoFeroz\Common\Entities\Etl\ImageEtl;
use App\ZapatoFeroz\Common\Entities\Etl\OptionEtl;
use App\ZapatoFeroz\Common\Entities\Etl\ProductEtl;
use App\ZapatoFeroz\Common\Entities\Etl\VariantEtl;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\Fields\ProductEtlFields;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\ValueObjects\Relation\ProductRelationMultiValueObject;

class ProductEtlTransform
{
    private array $pathImages;

    protected array $dataProducts = [];
    protected array $dataImages = [];
    protected array $dataOptions = [];
    protected array $dataVariants = [];

    public function __construct() {
        $this->pathImages = array_map(fn($value): string => "/$value", Storage::disk('public')->files(ImageHelper::IMAGES_FOLDER));
    }

    public function transform(LazyCollection $sourceData): ProductRelationMultiValueObject
    {
        foreach ($sourceData as $product) {
            $etlId = implode('_', array(
                $product->{ProductEtlFields::DOMAIN},
                $product->{ProductEtlFields::ID},
            ));

            $this->dataProducts[] = [
                ProductEtl::ETL_ID => $etlId,
                ProductEtl::DOMAIN => $product->{ProductEtlFields::DOMAIN},
                ProductEtl::ID => $product->{ProductEtlFields::ID},

                ProductEtl::TITLE => $product->{ProductEtlFields::TITLE},
                ProductEtl::HANDLE => $product->{ProductEtlFields::HANDLE},
                ProductEtl::VENDOR => $product->{ProductEtlFields::VENDOR},
                ProductEtl::PRODUCT_TYPE => !empty($product->{ProductEtlFields::PRODUCT_TYPE}) ? $product->{ProductEtlFields::PRODUCT_TYPE} : null,

                ProductEtl::CREATED => CarbonImmutable::parse($product->{ProductEtlFields::CREATED_AT})->setTimezone('UTC')->toDateTimeString(),
                ProductEtl::UPDATED => CarbonImmutable::parse($product->{ProductEtlFields::UPDATED_AT})->setTimezone('UTC')->toDateTimeString(),
                ProductEtl::PUBLISHED_AT => !empty($product->{ProductEtlFields::PUBLISHED_AT}) ? CarbonImmutable::parse($product->{ProductEtlFields::PUBLISHED_AT})->setTimezone('UTC')->toDateTimeString() : null,

                ProductEtl::PUBLISHED_SCOPE => $product->{ProductEtlFields::PUBLISHED_SCOPE},
                ProductEtl::STATUS => $product->{ProductEtlFields::STATUS},
                ProductEtl::TAGS => $product->{ProductEtlFields::TAGS},
            ];

            // IMAGES
            if (!empty($product->{ProductEtlFields::IMAGES})) {
                $this->imageTransform($etlId, $product->{ProductEtlFields::IMAGES});
            }

            // OPTIONS
            if (!empty($product->{ProductEtlFields::OPTIONS})) {
                $this->optionTransform($etlId, $product->{ProductEtlFields::OPTIONS});
            }

            // // VARIANTS
            if (!empty($product->{ProductEtlFields::VARIANTS})) {
                $this->variantTransform($etlId, $product->{ProductEtlFields::VARIANTS});
            }
        }

        return new ProductRelationMultiValueObject(
            $this->dataProducts,
            $this->dataImages,
            $this->dataOptions,
            $this->dataVariants
        );
    }

    private function imageTransform(string $productEtlId, array $images): void
    {
        foreach ($images as $image) {
            $imgName = md5($productEtlId .'_'. $image->{ProductEtlFields::ID});
            $imgPath = ImageHelper::getImagePathByName($imgName);

            try {
                if (CarbonImmutable::now()->isSameDay(CarbonImmutable::parse($image->{ProductEtlFields::UPDATED_AT}))) {
                    $imgPath = ImageHelper::importImageByUrl($image->{ProductEtlFields::SRC}, $imgName);
                    $this->pathImages[] = $imgPath;
                } elseif (!in_array($imgPath, $this->pathImages)) {
                    $imgPath = ImageHelper::importImageByUrl($image->{ProductEtlFields::SRC}, $imgName);
                    $this->pathImages[] = $imgPath;
                }
            } catch (\Exception $e) {
                $imgPath = null;
            }

            $this->dataImages[] = [
                ImageEtl::PRODUCT_ETL_ID => $productEtlId,
                ImageEtl::ID => $image->{ProductEtlFields::ID},

                ImageEtl::PRODUCT_ID => $image->{ProductEtlFields::PRODUCT_ID},
                ImageEtl::POSITION => $image->{ProductEtlFields::POSITION},
                ImageEtl::CREATED => $image->{ProductEtlFields::CREATED_AT},
                ImageEtl::UPDATED => $image->{ProductEtlFields::UPDATED_AT},
                ImageEtl::WIDTH => $image->{ProductEtlFields::WIDTH},
                ImageEtl::HEIGHT => $image->{ProductEtlFields::HEIGHT},
                ImageEtl::SRC => $image->{ProductEtlFields::SRC},
                ImageEtl::VARIANT_IDS => !empty($image->{ProductEtlFields::VARIANT_IDS}) ? json_encode($image->{ProductEtlFields::VARIANT_IDS}) : null,
                ImageEtl::PATH => $imgPath,
            ];
        }
    }

    private function optionTransform(string $productEtlId, array $options): void
    {
        foreach ($options as $option) {
            $this->dataOptions[] = [
                OptionEtl::PRODUCT_ETL_ID => $productEtlId,
                OptionEtl::ID => $option->{ProductEtlFields::ID},

                OptionEtl::PRODUCT_ID => $option->{ProductEtlFields::PRODUCT_ID},
                OptionEtl::NAME => $option->{ProductEtlFields::NAME},
                OptionEtl::POSITION => $option->{ProductEtlFields::POSITION},
                OptionEtl::VALUES => !empty($option->{ProductEtlFields::VALUES}) ? json_encode($option->{ProductEtlFields::VALUES}) : null,
            ];
        }
    }

    private function variantTransform(string $productEtlId, array $variants): void
    {
        foreach ($variants as $variant) {
            $this->dataVariants[] = [
                VariantEtl::PRODUCT_ETL_ID => $productEtlId,
                VariantEtl::ID => $variant->{ProductEtlFields::ID},

                VariantEtl::PRODUCT_ID => $variant->{ProductEtlFields::PRODUCT_ID},
                VariantEtl::SKU => !empty($variant->{ProductEtlFields::SKU}) ? $variant->{ProductEtlFields::SKU} : null,
                VariantEtl::TITLE => $variant->{ProductEtlFields::TITLE},
                VariantEtl::PRICE => $variant->{ProductEtlFields::PRICE},
                VariantEtl::POSITION => $variant->{ProductEtlFields::POSITION},
                VariantEtl::INVENTORY_POLICY => $variant->{ProductEtlFields::INVENTORY_POLICY},
                VariantEtl::COMPARE_AT_PRICE => !empty($variant->{ProductEtlFields::COMPARE_AT_PRICE}) ? $variant->{ProductEtlFields::COMPARE_AT_PRICE} : null,
                VariantEtl::OPTION_1 => $variant->{ProductEtlFields::OPTION1},
                VariantEtl::OPTION_2 => !empty($variant->{ProductEtlFields::OPTION2}) ? $variant->{ProductEtlFields::OPTION2} : null,
                VariantEtl::OPTION_3 => !empty($variant->{ProductEtlFields::OPTION3}) ? $variant->{ProductEtlFields::OPTION3} : null,
                VariantEtl::CREATED => CarbonImmutable::parse($variant->{ProductEtlFields::CREATED_AT})->setTimezone('UTC')->toDateTimeString(),
                VariantEtl::UPDATED => CarbonImmutable::parse($variant->{ProductEtlFields::UPDATED_AT})->setTimezone('UTC')->toDateTimeString(),
                VariantEtl::TAXABLE => $variant->{ProductEtlFields::TAXABLE},
                VariantEtl::BARCODE => !empty($variant->{ProductEtlFields::BARCODE}) ? $variant->{ProductEtlFields::BARCODE} : null,
                VariantEtl::FULFILLMENT_SERVICE => $variant->{ProductEtlFields::FULFILLMENT_SERVICE},
                VariantEtl::GRAMS => $variant->{ProductEtlFields::GRAMS},
                VariantEtl::INVENTORY_MANAGEMENT => !empty($variant->{ProductEtlFields::INVENTORY_MANAGEMENT}) ? $variant->{ProductEtlFields::INVENTORY_MANAGEMENT} : null,
                VariantEtl::REQUIRES_SHIPPING => $variant->{ProductEtlFields::REQUIRES_SHIPPING},
                VariantEtl::WEIGHT => $variant->{ProductEtlFields::WEIGHT},
                VariantEtl::WEIGHT_UNIT => $variant->{ProductEtlFields::WEIGHT_UNIT},
                VariantEtl::INVENTORY_ITEM_ID => $variant->{ProductEtlFields::INVENTORY_ITEM_ID},
                VariantEtl::INVENTORY_QUANTITY => $variant->{ProductEtlFields::INVENTORY_QUANTITY},
                VariantEtl::OLD_INVENTORY_QUANTITY => $variant->{ProductEtlFields::OLD_INVENTORY_QUANTITY},
                VariantEtl::IMAGE_ID => !empty($variant->{ProductEtlFields::IMAGE_ID}) ? $variant->{ProductEtlFields::IMAGE_ID} : null,
            ];
        }
    }
}
