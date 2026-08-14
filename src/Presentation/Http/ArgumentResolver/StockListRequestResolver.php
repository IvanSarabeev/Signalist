<?php

namespace App\Presentation\Http\ArgumentResolver;

use App\Presentation\Http\Request\Stock\StockListRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AsTargetedValueResolver('stock_list_request')]
final class StockListRequestResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== StockListRequest::class) {
            return [];
        }

        $symbol = trim($request->query->get('symbol', ''));

        yield new StockListRequest(
            $symbol !== '' ? $symbol : null,
        );
    }
}
