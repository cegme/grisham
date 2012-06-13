#include <fstream>
#include <iostream>
#include <ostream>
#include <stdlib.h>
#include <stdio.h>
#include <string>
#include <cstring>
#include <vector>


void split(char* line, std::vector<std::string>& v) {
	// Unsafe, be careful not to modify _line

	char *pch;
	pch = strtok(line, ",");
	while (pch != NULL) {
		v.push_back(pch);
		pch = strtok(NULL, ",");
	}
}

void replace(std::string& target, const std::string oldstr, const std::string newstr) { 
	size_t x;
	for(x = target.find(oldstr); x != std::string::npos; x = target.find(oldstr,x)) {
		target.replace(x, oldstr.length(), newstr);
		x += newstr.length();
	}
}


int main (int argc, char* argv[]) {

	using namespace std;

	string infile;
	string outdir;

	if(argc == 1 || argc < 3) {
		cerr << "Compile: g++ -03 -o parseDBLP parseDBLP.cpp\n";
		cerr << "Usage: ./parseDBLP <DBLP.text> <filesdir>\n"
					<< "\tBe sure to end <filesdir> with a slash\n"
					<< "\ttime ./parseDBLP /home/cgrant/data/DBLP/DBLPOnlyCitationOct19.txt ./";
		exit(1);
	}

	// input file
	ifstream in(argv[1]);	
	if (!in.is_open()) exit(2);

	string a(argv[2]); a.append("author.csv");
	string p(argv[2]); p.append("paper.csv");
	string r(argv[2]); r.append("reference.csv");

	// output files
	ofstream authorcsv(a.c_str());
	ofstream papercsv(p.c_str());
	ofstream referencecsv(r.c_str());

	// Write header files
	authorcsv << "pid|person\n";
	papercsv << "pid|papertitle|pubyear|venue|abstract\n";
	referencecsv << "pid|citation\n";


	/*
	 *  Here are the states:
	 * 
	 * #* --- paperTitle
	 * #@ --- Authors
	 * #t ---- Year
	 * #c  --- publication venue
	 * #index 00---- index id of this paper
	 * #% ---- the id of references of this paper (there are multiple lines, with each indicating a reference)
	 * #! --- Abstract
	 *
	 */

	string title;
	vector<string> authors;
	string year;
	string venue;
	string index;
	vector<string> citations;
	string abstract;

	string line;
	int count = 0;

	// Skip the first line bc it is a count (1632442)
	getline(in, line);
	while(getline(in, line)) {
		if(++count % 1000000 == 0)  cerr << "." <<endl;

		if(line == "") {

			// Append to papers
			// If a column contains a | or a " then we need to quote the whole column 2x the quotes
			bool aquote = false, tquote=false;
			if(abstract.find("|") != string::npos || abstract.find("\"") != string::npos) {
				aquote = true;
				replace(abstract,"\"", "\"\""); // 2x on the quotes " --> ""
			}
			if(title.find("|") != string::npos || title.find("\"") != string::npos) {
				tquote = true;
				replace(title,"\"", "\"\""); // 2x on the quotes " --> ""
			}

			papercsv << index << "|" ; 
			if (tquote) { papercsv << "\"" << title << "\"|"; } else { papercsv << title << "|"; }
			papercsv << year << "|"<< venue << "|";
			if (aquote) { papercsv << "\"" << abstract << "\"\n"; } else { papercsv << abstract << "\n"; }

			// Append to authors
			for (int i =0; i < authors.size(); ++i) {
				authorcsv << index << "|" << authors[i] << "\n";
			}

			// Append to references
			for (int i =0; i < citations.size(); ++i) {
				referencecsv << index << "|" << citations[i] << "\n";
			}

			// Reset data structures
			authors.clear();
			citations.clear();
			title = "";
			year = "";
			venue = "";
			index = "";
			abstract = "";
		}
		else {
			switch ( line[1] ) {
				case '*': 
					title = line.substr(2);
					break;

				case '@':
					char* tmp;
					tmp = &line[2];
					split(tmp, authors);
					break;

				case 't':
					year = line.substr(2);
					break;

				case 'c':
					venue = line.substr(2);
					break;

				case 'i':
					index = line.substr(6);
					break;

				case '%':
					citations.push_back(line.substr(2));
					break;

				case '!':
					abstract = line.substr(2);
					break;

				default:
					cerr << "Bad line["<<count<<"]: " << line << " | Ignoring...\n";
					exit(3);
			}
		}
	}
	

	// Close all files
	in.close();
	authorcsv.close();
	referencecsv.close();
	return 0;
}
