<?php

namespace Modules\Shopify\Traits;

trait GraphqlQueryTrait{
    protected function GetProduct($first_last, $sortBy, $cursor, $link, $query = "\"\""): array
    {
        $query = "{
                      products({$first_last}: {$sortBy}, {$cursor}: {$link}, query: $query, reverse: true) {
                        nodes {
                          id
                          title
                          tags
                          vendor
                          images(first: 1) {
                              nodes {
                                  id
                                  url
                                }
                          }
                        }
                        pageInfo {
                          hasNextPage
                          endCursor
                          hasPreviousPage
                          startCursor
                        }
                      }
                    }";
        return ["query" => $query];
    }
    protected function GetVariants($first_last, $sortBy, $cursor, $link, $query = "\"\""): array
    {
        $query = "{
                      products({$first_last}: {$sortBy}, {$cursor}: {$link}, query: $query, reverse: true) {
                        nodes{
                          id
                          variants(first: 20) {
                            edges {
                              node {
                                id
                                title
                                price
                                compareAtPrice
                                image{
                                  id
                                  url
                                }
                              }
                            }
                          }
                        }
                      }
                    }";
        return ["query" => $query];
    }

    protected function GetVendorsAngTags(): array
    {
        $query = "{
                shop {
                        productVendors(first: 25){
                          edges{
                            node
                          }
                        }
                        productTags(first: 250){
                          edges{
                            node
                          }
                        }
                    }
                }";
        return ["query" => $query];
    }

    protected function GetProductVariant($vid): array
    {
        $query = "
               {
                  productVariant(id: \"{$vid}\") {
                        id
                        title
                        price
                        compareAtPrice
                      }
                }
        ";
        return ["query" => $query];
    }

    protected function GetUpdateVariants($variant_id, $price, $cap): array
    {
        if($cap != ''){
            $compare_at_price = "\"$cap\"";
        }else{
            $compare_at_price = "null";
        }
        $input = "input: {
                    id: \"{$variant_id}\"
                    price: \"{$price}\"
                    compareAtPrice: {$compare_at_price}
                  }";
        $query = "mutation {
                            productVariantUpdate({$input}) {
                                productVariant {
                                    id
                                    title
                                    price
                                    compareAtPrice
                                }
                                userErrors {
                                    field
                                    message
                                }
                            }
                     }";
        return ["query" => $query];
    }
    protected function GetCollectionId(): array
    {
        $query = "{
                      collections(first: 10) {
                          nodes {
                            id
                            title
                          }
                        }
                    }";
        return ["query" => $query];
    }
    protected function GetCollectionProducts($collection_id, $first_last, $sortBy, $cursor, $link): array
    {
        $query = "{
                        collection(id:\"{$collection_id}\"){
                            id
                            products ({$first_last}: {$sortBy}, {$cursor}: {$link}, reverse: true){
                                nodes{
                                    id
                                    title
                                    vendor
                                    images(first: 1) {
                                      nodes {
                                          id
                                          url
                                        }
                                    }
                               }
                                pageInfo {
                                  hasNextPage
                                  endCursor
                                  hasPreviousPage
                                  startCursor
                               }
                           }                    
                       }
                   }";
        return ["query" => $query];
    }
    protected function GetCollectionProductVariants($collection_id, $first_last, $sortBy, $cursor, $link): array
    {
        $query = "{
                        collection(id:\"{$collection_id}\"){
                            id
                            products ({$first_last}: {$sortBy}, {$cursor}: {$link}, reverse: true){
                                nodes{
                                    id
                                    variants(first: 20) {
                                        edges {
                                          node {
                                            id
                                            title
                                            price
                                            compareAtPrice
                                            image{
                                              id
                                              url
                                            }
                                          }
                                        }
                                    }
                                }    
                           }                    
                       }
                   }";
        return ["query" => $query];
    }
}