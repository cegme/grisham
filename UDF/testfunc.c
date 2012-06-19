#include "postgres.h"
#include "fmgr.h"
#include "utils/builtins.h"
#include <math.h>
#include <stdio.h>

#ifdef PG_MODULE_MAGIC
PG_MODULE_MAGIC;
#endif


int
sum2(int a, int b)
{
    return a + b;
}



float4
sum3(int a, int b)
{

    float4 res = a + b + 1.0;
    return res;
}


float8 
value4(float8* a, float8* b)
{
    float8 * res = (float8*)palloc(sizeof(float8));
    *res =  *a +1.0;
    return *res;
}


/* by reference, fixed length */

PG_FUNCTION_INFO_V1(add_one_float4);

Datum
add_one_float4(PG_FUNCTION_ARGS)
{
    /* The macros for FLOAT8 hide its pass-by-reference nature. */
    float4   arg = PG_GETARG_FLOAT4(0);
    //elog(DEBUG, "HELLO");
    PG_RETURN_FLOAT4(arg + 1.0);
}

