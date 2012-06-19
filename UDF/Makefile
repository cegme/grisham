INCLUDE1 = /usr/include/postgresql/
all: 

	gcc -shared -fpic -o testfunc.so testfunc.c -I$(INCLUDE1) -I/home/virup/postgresql-9.1.4/src/include

cgrant:
	gcc -shared -fpic -o testfunc.so testfunc.c -I$(INCLUDE1) -I/usr/include/postgresql/9.1/server/

clean:
	rm testfunc.so

