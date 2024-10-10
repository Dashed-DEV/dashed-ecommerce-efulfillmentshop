<form wire:submit.prevent="submit" class="grid gap-2">
    @if($order->efulfillmentShopOrder)
        @if($order->efulfillmentShopOrder->pushed == 1)
            <span
                class="w-full justify-center bg-green-100 text-green-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling naar E-fulfillment Shop gepushed
                                </span>
        @elseif($order->efulfillmentShopOrder->pushed == 2)
            <span
                class="w-full justify-center bg-red-100 text-red-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling niet naar E-fulfillment Shop gepushed (Fout: {{ $order->efulfillmentShopOrder->error }})
                                </span>
            <button type="submit"

                    style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                    class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action">                Opnieuw naar E-fulfillment Shop pushen
            </button>
        @else
            <span
                class="w-full justify-center bg-yellow-100 text-yellow-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling wordt naar E-fulfillment Shop gepushed
                                </span>
        @endif
        @else
        <span
            class="w-full justify-center bg-yellow-100 text-yellow-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling niet gekoppeld aan E-fulfillment shop
                                </span>
    @endif
</form>
