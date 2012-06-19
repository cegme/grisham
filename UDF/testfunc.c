#include "postgres.h"
#include "fmgr.h"
#include "utils/builtins.h"
#include "utils/array.h"
#include "utils/datum.h"
#include "utils/numeric.h"
#include "catalog/pg_type.h"
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

//Datum
//array_sum(PG_FUNCTION_ARGS)
//{
//	int_array* arg0 = PG_GETARG_INT64_P(0);
//	int_array* arg1 = PG_GETARG_INT64_P(1);
//
//	int4 arg0len = VARSIZE(arg0);
//	int4 arg1len = VARSIZE(arg1);
//
//	PG_RETURN_INT32(arg0len + arg1len);
//}



PG_FUNCTION_INFO_V1(compute);

Datum
compute(PG_FUNCTION_ARGS)
{
    float4 *array1 = (float4*)ARR_DATA_PTR(PG_GETARG_ARRAYTYPE_P(0));
    int array1len = ARR_SIZE(PG_GETARG_ARRAYTYPE_P(0));

    float8 *array2 = (float8*)ARR_DATA_PTR(PG_GETARG_ARRAYTYPE_P(1));
    int array2len = ARR_DIMS(PG_GETARG_ARRAYTYPE_P(1))[0];

    ArrayType *result;

    result = (ArrayType *)palloc0(sizeof(int) + array2len*sizeof(float8));
    SET_VARSIZE(result, sizeof(int) + sizeof(float8) * array1len);
    int i;
    //for(i = 0; i < array1len; i++)
    //    ((float8*)ARR_DATA_PTR(result))[i] =  array1[i] + array2[i];

    //PG_RETURN_ARRAYTYPE_P(result);
    PG_RETURN_FLOAT4(((float4*)array1)[1]);

}


PG_FUNCTION_INFO_V1(RetNull);

Datum
RetNull(PG_FUNCTION_ARGS)
{
    ArrayType *pgarray;
    unsigned int size = PG_GETARG_INT32(0);
    float8* array = (float8*) palloc (sizeof(float8)*size);
    memset(array, 0, sizeof(int)*size);
    pgarray = construct_array((Datum *)array,size,FLOAT8OID,sizeof(float8),true,'d');

    PG_RETURN_ARRAYTYPE_P(pgarray);

}

