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


PG_FUNCTION_INFO_V1(add_two_float4);

Datum
add_two_float4(PG_FUNCTION_ARGS)
{
    /* The macros for FLOAT8 hide its pass-by-reference nature. */
    float4   arg1= PG_GETARG_FLOAT4(0);
    float4   arg2= PG_GETARG_FLOAT4(1);
    //elog(DEBUG, "HELLO");
    PG_RETURN_FLOAT4(arg1 + arg2 + 2.0);
}

typedef struct {
	int4 length;
	int4 data[1];
} int_array;



PG_FUNCTION_INFO_V1(array_sum);

Datum
array_sum(PG_FUNCTION_ARGS)
{
	int_array* arg0 = PG_GETARG_INT64_P(0);
	int_array* arg1 = PG_GETARG_INT64_P(1);

	int4 arg0len = VARSIZE(arg0);
	int4 arg1len = VARSIZE(arg1);

	PG_RETURN_INT32(arg0len + arg1len);



}

