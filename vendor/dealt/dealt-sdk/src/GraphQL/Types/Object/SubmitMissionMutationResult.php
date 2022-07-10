<?php

namespace Dealt\DealtSDK\GraphQL\Types\Object;

class SubmitMissionMutationResult extends AbstractUnionType
{
    public static $unionName       = 'SubmitMissionMutation_Result';
    public static $unionDefinition = [
        SubmitMissionMutationSuccess::class,
        SubmitMissionMutationFailure::class,
    ];
}
