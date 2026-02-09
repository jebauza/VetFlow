<?php

namespace App\ZapatoFeroz\Syschronize\Shopify\Etl\Transformers;

use Illuminate\Support\LazyCollection;
use App\ZapatoFeroz\Common\Entities\Etl\OrderEtl;
use App\ZapatoFeroz\Common\Entities\Etl\RefundEtl;
use App\ZapatoFeroz\Common\Entities\Etl\OrderItemEtl;
use App\ZapatoFeroz\Common\Entities\Etl\RefundItemEtl;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\Fields\OrderEtlFields;
use App\ZapatoFeroz\Syschronize\Shopify\Etl\ValueObjects\Relation\OrderRelationMultiValueObject;
use Carbon\CarbonImmutable;

class OrderEtlTransform
{
    protected array $dataOrders = [];
    protected array $dataOrderItems = [];
    protected array $dataRefunds = [];
    protected array $dataRefundItems = [];

    public function transform(LazyCollection $sourceData): OrderRelationMultiValueObject
    {
        foreach ($sourceData as $order) {
            $etlId = implode('_', array(
                $order->{OrderEtlFields::DOMAIN},
                $order->{OrderEtlFields::ID},
            ));

            $this->dataOrders[] = [
                OrderEtl::ETL_ID => $etlId,
                OrderEtl::DOMAIN => $order->{OrderEtlFields::DOMAIN},
                OrderEtl::ID => $order->{OrderEtlFields::ID},

                OrderEtl::CUSTOMER_ID => !empty($order->{OrderEtlFields::CUSTOMER}->{OrderEtlFields::ID}) ? $order->{OrderEtlFields::CUSTOMER}->{OrderEtlFields::ID} : null,
                OrderEtl::NUMBER => $order->{OrderEtlFields::NUMBER},
                OrderEtl::ORDER_NUMBER => $order->{OrderEtlFields::ORDER_NUMBER},
                OrderEtl::FINANCIAL_STATUS => !empty($order->{OrderEtlFields::FINANCIAL_STATUS}) ? $order->{OrderEtlFields::FINANCIAL_STATUS} : null,
                OrderEtl::FULFILLMENT_STATUS => !empty($order->{OrderEtlFields::FULFILLMENT_STATUS}) ? $order->{OrderEtlFields::FULFILLMENT_STATUS} : null,
                OrderEtl::CURRENCY => $order->{OrderEtlFields::CURRENCY},
                OrderEtl::PRESENTMENT_CURRENCY => $order->{OrderEtlFields::PRESENTMENT_CURRENCY},

                OrderEtl::CREATED => CarbonImmutable::parse($order->{OrderEtlFields::CREATED_AT})->setTimezone('UTC')->toDateTimeString(),
                OrderEtl::UPDATED => !empty($order->{OrderEtlFields::UPDATED_AT}) ? CarbonImmutable::parse($order->{OrderEtlFields::UPDATED_AT})->setTimezone('UTC')->toDateTimeString() : null,
                OrderEtl::PROCESSED_AT => !empty($order->{OrderEtlFields::PROCESSED_AT}) ? CarbonImmutable::parse($order->{OrderEtlFields::PROCESSED_AT})->setTimezone('UTC')->toDateTimeString() : null,
                OrderEtl::CLOSED_AT => !empty($order->{OrderEtlFields::CLOSED_AT}) ? CarbonImmutable::parse($order->{OrderEtlFields::CLOSED_AT})->setTimezone('UTC')->toDateTimeString() : null,
                OrderEtl::CANCELLED_AT => !empty($order->{OrderEtlFields::CANCELLED_AT}) ? CarbonImmutable::parse($order->{OrderEtlFields::CANCELLED_AT})->setTimezone('UTC')->toDateTimeString() : null,
                OrderEtl::DATES => json_encode([
                    OrderEtlFields::CREATED_AT => $order->{OrderEtlFields::CREATED_AT},
                    OrderEtlFields::UPDATED_AT => $order->{OrderEtlFields::UPDATED_AT},
                    OrderEtlFields::PROCESSED_AT => $order->{OrderEtlFields::PROCESSED_AT},
                    OrderEtlFields::CLOSED_AT => $order->{OrderEtlFields::CLOSED_AT},
                    OrderEtlFields::CANCELLED_AT => $order->{OrderEtlFields::CANCELLED_AT},
                ]),

                OrderEtl::CANCEL_REASON => !empty($order->{OrderEtlFields::CANCEL_REASON}) ? $order->{OrderEtlFields::CANCEL_REASON} : null,
                OrderEtl::REFERENCE => !empty($order->{OrderEtlFields::REFERENCE}) ? $order->{OrderEtlFields::REFERENCE} : null,
                OrderEtl::CURRENT_TOTAL_DISCOUNTS => $order->{OrderEtlFields::CURRENT_TOTAL_DISCOUNTS},
                OrderEtl::CURRENT_SUBTOTAL_PRICE => $order->{OrderEtlFields::CURRENT_SUBTOTAL_PRICE},
                OrderEtl::CURRENT_TOTAL_PRICE => $order->{OrderEtlFields::CURRENT_TOTAL_PRICE},
                OrderEtl::CURRENT_TOTAL_TAX => $order->{OrderEtlFields::CURRENT_TOTAL_TAX},
                OrderEtl::TOTAL_LINE_ITEMS_PRICE => $order->{OrderEtlFields::TOTAL_LINE_ITEMS_PRICE},
                OrderEtl::TOTAL_OUTSTANDING => $order->{OrderEtlFields::TOTAL_OUTSTANDING},
                OrderEtl::TOTAL_TIP_RECEIVED => $order->{OrderEtlFields::TOTAL_TIP_RECEIVED},
                OrderEtl::TOTAL_DISCOUNTS => $order->{OrderEtlFields::TOTAL_DISCOUNTS},
                OrderEtl::SUBTOTAL_PRICE => $order->{OrderEtlFields::SUBTOTAL_PRICE},
                OrderEtl::TAXES_INCLUDED => $order->{OrderEtlFields::TAXES_INCLUDED},
                OrderEtl::TOTAL_PRICE => $order->{OrderEtlFields::TOTAL_PRICE},
                OrderEtl::TOTAL_TAX => $order->{OrderEtlFields::TOTAL_TAX},
                OrderEtl::TAGS => !empty($order->{OrderEtlFields::TAGS}) ? json_encode($order->{OrderEtlFields::TAGS}) : null,
                OrderEtl::BILLING_ADDRESS => !empty($order->{OrderEtlFields::BILLING_ADDRESS}) ? json_encode($order->{OrderEtlFields::BILLING_ADDRESS}) : null,
                OrderEtl::SHIPPING_ADDRESS => !empty($order->{OrderEtlFields::SHIPPING_ADDRESS}) ? json_encode($order->{OrderEtlFields::SHIPPING_ADDRESS}) : null,
            ];
            // QueryHelper::insertModel(OrderEtl::class, end($this->dataOrders));

            // ORDER_ITEM
            if (!empty($order->{OrderEtlFields::LINE_ITEMS})) {
                $this->orderItemTransform($etlId, $order->{OrderEtlFields::LINE_ITEMS});
            }

            // REFUND
            if (!empty($order->{OrderEtlFields::REFUNDS})) {
                $this->refundTransform($etlId, $order->{OrderEtlFields::REFUNDS});
            }
        }

        return new OrderRelationMultiValueObject(
            $this->dataOrders,
            $this->dataOrderItems,
            $this->dataRefunds,
            $this->dataRefundItems
        );
    }

    private function orderItemTransform(string $orderEtlId, array $orderItems): void
    {
        foreach ($orderItems as $orderItem) {
            $this->dataOrderItems[] = [
                OrderItemEtl::ORDER_ETL_ID => $orderEtlId,
                OrderItemEtl::ID => $orderItem->{OrderEtlFields::ID},

                OrderItemEtl::SKU => !empty($orderItem->{OrderEtlFields::SKU}) ? $orderItem->{OrderEtlFields::SKU} : null,
                OrderItemEtl::NAME => $orderItem->{OrderEtlFields::NAME},
                OrderItemEtl::TITLE => $orderItem->{OrderEtlFields::TITLE},
                OrderItemEtl::PRODUCT_EXISTS => $orderItem->{OrderEtlFields::PRODUCT_EXISTS},
                OrderItemEtl::PRODUCT_ID => !empty($orderItem->{OrderEtlFields::PRODUCT_ID}) ? $orderItem->{OrderEtlFields::PRODUCT_ID} : null,
                OrderItemEtl::VARIANT_ID => !empty($orderItem->{OrderEtlFields::VARIANT_ID}) ? $orderItem->{OrderEtlFields::VARIANT_ID} : null,
                OrderItemEtl::VARIANT_INVENTORY_MANAGEMENT => !empty($orderItem->{OrderEtlFields::VARIANT_INVENTORY_MANAGEMENT}) ? $orderItem->{OrderEtlFields::VARIANT_INVENTORY_MANAGEMENT} : null,
                OrderItemEtl::VARIANT_TITLE => !empty($orderItem->{OrderEtlFields::VARIANT_TITLE}) ? $orderItem->{OrderEtlFields::VARIANT_TITLE} : null,
                OrderItemEtl::PROPERTIES => !empty($orderItem->{OrderEtlFields::PROPERTIES}) ? json_encode($orderItem->{OrderEtlFields::PROPERTIES}) : null,
                OrderItemEtl::QUANTITY => $orderItem->{OrderEtlFields::QUANTITY},
                OrderItemEtl::PRICE => $orderItem->{OrderEtlFields::PRICE},
                OrderItemEtl::PRICE_SET => !empty($orderItem->{OrderEtlFields::PRICE_SET}) ? json_encode($orderItem->{OrderEtlFields::PRICE_SET}) : null,
                OrderItemEtl::TOTAL_DISCOUNT => $orderItem->{OrderEtlFields::TOTAL_DISCOUNT},
                OrderItemEtl::TOTAL_DISCOUNT_SET => !empty($orderItem->{OrderEtlFields::TOTAL_DISCOUNT_SET}) ? json_encode($orderItem->{OrderEtlFields::TOTAL_DISCOUNT_SET}) : null,
                OrderItemEtl::DISCOUNT_ALLOCATIONS => !empty($orderItem->{OrderEtlFields::DISCOUNT_ALLOCATIONS}) ? json_encode($orderItem->{OrderEtlFields::DISCOUNT_ALLOCATIONS}) : null,
                OrderItemEtl::TAX_LINES => !empty($orderItem->{OrderEtlFields::TAX_LINES}) ? json_encode($orderItem->{OrderEtlFields::TAX_LINES}) : null,
                OrderItemEtl::DUTIES => !empty($orderItem->{OrderEtlFields::DUTIES}) ? json_encode($orderItem->{OrderEtlFields::DUTIES}) : null,
                OrderItemEtl::REQUIRES_SHIPPING => $orderItem->{OrderEtlFields::REQUIRES_SHIPPING},
                OrderItemEtl::TAXABLE => $orderItem->{OrderEtlFields::TAXABLE},
                OrderItemEtl::FULFILLABLE_QUANTITY => $orderItem->{OrderEtlFields::FULFILLABLE_QUANTITY},
                OrderItemEtl::FULFILLMENT_SERVICE => $orderItem->{OrderEtlFields::FULFILLMENT_SERVICE},
                OrderItemEtl::FULFILLMENT_STATUS => !empty($orderItem->{OrderEtlFields::FULFILLMENT_STATUS}) ? $orderItem->{OrderEtlFields::FULFILLMENT_STATUS} : null,
                OrderItemEtl::GIFT_CARD => $orderItem->{OrderEtlFields::GIFT_CARD},
                OrderItemEtl::GRAMS => $orderItem->{OrderEtlFields::GRAMS},
                OrderItemEtl::ATTRIBUTED_STAFFS => !empty($orderItem->{OrderEtlFields::ATTRIBUTED_STAFFS}) ? json_encode($orderItem->{OrderEtlFields::ATTRIBUTED_STAFFS}) : null,
                OrderItemEtl::VENDOR => !empty($orderItem->{OrderEtlFields::VENDOR}) ? $orderItem->{OrderEtlFields::VENDOR} : null,
            ];
        }
    }

    private function refundTransform(string $orderEtlId, array $refunds): void
    {
        foreach ($refunds as $refund) {
            $etlId = implode('_', array(
                $orderEtlId,
                $refund->{OrderEtlFields::ID},
            ));

            $totalTransactions = 0;
            if (!empty($refund->{OrderEtlFields::TRANSACTIONS})) {
                foreach ($refund->{OrderEtlFields::TRANSACTIONS} as $transaction) {
                    $totalTransactions += ((float) $transaction->{OrderEtlFields::AMOUNT}) ?? 0;
                }
            }

            $this->dataRefunds[] = [
                RefundEtl::ETL_ID => $etlId,
                RefundEtl::ORDER_ETL_ID => $orderEtlId,
                RefundEtl::ID => $refund->{OrderEtlFields::ID},

                RefundEtl::ORDER_ID => $refund->{OrderEtlFields::ORDER_ID},
                RefundEtl::USER_ID => !empty($refund->{OrderEtlFields::USER_ID}) ? $refund->{OrderEtlFields::USER_ID} : null,
                RefundEtl::NOTE => !empty($refund->{OrderEtlFields::NOTE}) ? $refund->{OrderEtlFields::NOTE} : null,
                RefundEtl::CREATED => CarbonImmutable::parse($refund->{OrderEtlFields::CREATED_AT})->setTimezone('UTC')->toDateTimeString(),
                RefundEtl::PROCESSED_AT => CarbonImmutable::parse($refund->{OrderEtlFields::PROCESSED_AT})->setTimezone('UTC')->toDateTimeString(),
                RefundEtl::RESTOCK => $refund->{OrderEtlFields::RESTOCK},
                RefundEtl::TOTAL_DUTIES_SET => !empty($refund->{OrderEtlFields::TOTAL_DUTIES_SET}) ? json_encode($refund->{OrderEtlFields::TOTAL_DUTIES_SET}) : null,
                RefundEtl::ORDER_ADJUSTMENTS => !empty($refund->{OrderEtlFields::ORDER_ADJUSTMENTS}) ? json_encode($refund->{OrderEtlFields::ORDER_ADJUSTMENTS}) : null,
                RefundEtl::TRANSACTIONS => !empty($refund->{OrderEtlFields::TRANSACTIONS}) ? json_encode($refund->{OrderEtlFields::TRANSACTIONS}) : null,
                RefundEtl::TOTAL_TRANSACTIONS => $totalTransactions,
                RefundEtl::DUTIES => !empty($refund->{OrderEtlFields::DUTIES}) ? json_encode($refund->{OrderEtlFields::DUTIES}) : null,
            ];

            // REFUND ITEM
            if (!empty($refund->{OrderEtlFields::REFUND_LINE_ITEMS})) {
                $this->refundItemTransform($etlId, $refund->{OrderEtlFields::REFUND_LINE_ITEMS});
            }
        }
    }

    private function refundItemTransform(string $refundEtlId, array $refundItems): void
    {
        foreach ($refundItems as $refundItem) {
            $this->dataRefundItems[] = [
                RefundItemEtl::REFUND_ETL_ID => $refundEtlId,
                RefundItemEtl::ID => $refundItem->{OrderEtlFields::ID},

                RefundItemEtl::ORDER_ITEM_ID => $refundItem->{OrderEtlFields::LINE_ITEM_ID},
                RefundItemEtl::LOCATION_ID => !empty($refundItem->{OrderEtlFields::LOCATION_ID}) ? $refundItem->{OrderEtlFields::LOCATION_ID} : null,
                RefundItemEtl::QUANTITY => $refundItem->{OrderEtlFields::QUANTITY},
                RefundItemEtl::RESTOCK_TYPE => $refundItem->{OrderEtlFields::RESTOCK_TYPE},
                RefundItemEtl::SUBTOTAL => $refundItem->{OrderEtlFields::SUBTOTAL},
                RefundItemEtl::SUBTOTAL_SET => !empty($refundItem->{OrderEtlFields::SUBTOTAL_SET}) ? json_encode($refundItem->{OrderEtlFields::SUBTOTAL_SET}) : null,
                RefundItemEtl::TOTAL_TAX => $refundItem->{OrderEtlFields::TOTAL_TAX},
                RefundItemEtl::TOTAL_TAX_SET => !empty($refundItem->{OrderEtlFields::TOTAL_TAX_SET}) ? json_encode($refundItem->{OrderEtlFields::TOTAL_TAX_SET}) : null,
                RefundItemEtl::ITEM => !empty($refundItem->{OrderEtlFields::LINE_ITEM}) ? json_encode($refundItem->{OrderEtlFields::LINE_ITEM}) : null,
            ];
        }
    }
}
