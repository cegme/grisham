#!/usr/bin/python

import itertools
from collections import defaultdict

# k is cited by v
ref_backwards = [(1,2),
(2,3),
(3,2),
(4,1),
(4,2),
(5,1),
(5,2),
(6,5),
(6,2),
(6,3)]


if __name__ == '__main__':

	ref = map(lambda x: (x[1], x[0]), ref_backwards) # flip ref_backwards
	print "ref_backwards:", ref_backwards
	print "ref:", ref

	citations = defaultdict(list)

	for (p,c) in ref:
		citations[p].append(c)
	print "citations:", citations

	scoremap = defaultdict(float)
	scoremap.update(dict(map(lambda x: (x[0], len(x[1])), citations.items())))
	#scoremap = dict(map(lambda x: (x[0], len(x[1])), citations.items()))
	print "scoremap:", scoremap

	for level in range(1,6):
		# Sum the values in the score map for each key in citations
		update = dict(map(lambda (paper,citedby):
									(paper,
										sum(map(lambda z: scoremap[z], citedby))
									), 
									citations.items()))
		print "update:", update
		for k,v in scoremap.items():
			if (v > 0.0):	scoremap[k] += (update[k]/(float(level+1)**1))

		print level, scoremap



