#!/usr/bin/env python
# -*- coding: UTF-8 -*-
import os
import re
import sys
import logging
import codecs
import numpy as np

from nltk.tokenize import regexp_tokenize
from nltk.stem.wordnet import WordNetLemmatizer
from collections import defaultdict
from gensim import corpora, models, similarities

logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)
MIN_FREQUENCY = 15

money_regex = re.compile(r'^\$?(\d*(\d\.?|\.\d{1,2}))$')
alpha_num_regex = re.compile(r'^[A-Za-z0-9_-]*$')
num_regex = re.compile(r'^[0-9]*$')

LMTZR = WordNetLemmatizer()

def is_ascii(text):
    try:
        text.decode('ascii')
    except UnicodeDecodeError:
        return False
    else:
        return True

def cleanup(token):
    
    STRIP_CHAR_LIST = [u'_', u'-', u',', u'!', u':', u'.', u'?', 
                       u';', u'=', u'…', u'•', u'–', u'¿', u'¡', 
                       u'º', u'ª', u'«', u'»', u'*'] 
    
    token = re.sub('[\(\)\{\}\[\]\'\"\\\/*<>|]', '', token)    
    token = token.replace(u'“', u'').replace(u'”', u'').replace(u'—', u'')

    for each_char in STRIP_CHAR_LIST:
        token = token.strip(each_char)
    
    return token


def money(token):
    return (money_regex.match(token) is not None)

def alpha_num(token):
    return (alpha_num_regex.match(token) is not None)

def num(token):
    return (num_regex.match(token) is not None)

def tokenize_text(page_text):
    '''
    Tokenizes text using NLTK and regEx   
    '''

    pattern = r'''(?:[A-Z][.])+|([1-9]|1[0-2]|0[1-9]){1}(:[0-5][0-9][aApP][mM]){1}|([0]?[1-9]|[1|2][0-9]|[3][0|1])[./-]([0]?[1-9]|[1][0-2])[./-]([0-9]{4}|[0-9]{2})|[$?|\-?]\d[\d,.:\^\-/\d]*\d|((mailto\:|(news|(ht|f)tp(s?))\://){1}\S+)|\w+[\w\-\#\@\'.&$]*\w+|[\@|\#|\&]?\w+(\w+)?|[:punct:]'''

    
    tokens = regexp_tokenize(page_text.strip().lower(), pattern)
    tokens = [cleanup(w) for w in tokens]
    
    tokens = [w for w in tokens if ((len(w) > 1) and (money(w) or alpha_num(w)))] 

    tokens = [LMTZR.lemmatize(w) for w in tokens]

    return tokens;



def process_line(line):
    
    ss = line.strip().split(u'|')
    line_text = ' '
    if len(ss) > 4: 
        line_text = ss[1] + u' ' + ss[3] + u' ' + ss[4]
    elif len(ss) > 3: 
        line_text = ss[1] + u' ' + ss[3]
    elif len(ss) > 1: 
        line_text = ss[1]
    
    tokens = tokenize_text(line_text) 
    
   
    return tokens
    


def load_stop_words(file_name):
    
    stopwords = list();
    with open(file_name, "r") as fSW: 
        for line in fSW: 
            stopwords.append(line.strip().lower())
    return stopwords

def create_dictionary(en_sw_file, surveys_file, dictionary_file):
    
    # loads stop words 
    stoplist = load_stop_words(en_sw_file)
    
    # collect statistics about all tokens
    dictionary = corpora.Dictionary(process_line(line) for line in codecs.open(surveys_file, mode='r', encoding='utf-8'))
    
    # remove stop words and words that appear only once
    stop_ids = [dictionary.token2id[stopword] for stopword in stoplist if stopword in dictionary.token2id]
    once_ids = [tokenid for tokenid, docfreq in dictionary.dfs.iteritems() if docfreq < MIN_FREQUENCY]
    
    dictionary.filter_tokens(stop_ids + once_ids) # remove stop words and words that appear only once
    dictionary.compactify() # remove gaps in id sequence after words that were removed
    dictionary.save(dictionary_file) # store the dictionary, for future reference
    
    print dictionary

            
class TextCorpus(object):
    
    def __init__(self, _dictionary, _survey_questions_file):
        
        self.surveys_file = _survey_questions_file                
        self.dictionary = _dictionary
         
    def __iter__(self):

        for line in codecs.open(self.surveys_file, mode='r', encoding='utf-8'):
            yield self.dictionary.doc2bow(process_line(line))


def run_lda(dictionary_file, ldac_file, theta_file, topics_file, num_topics, num_passes, paper_ids):
    
    dictionary = corpora.Dictionary().load(dictionary_file) 
    corpus_ldac = corpora.BleiCorpus(fname=ldac_file, fname_vocab=(ldac_file + '.vocab'))  
    num_docs = len(corpus_ldac)
        
    model = models.ldamodel.LdaModel(corpus_ldac, id2word=dictionary, 
                                     num_topics=num_topics, passes=num_passes, 
                                     update_every=0,
                                     alpha=1.0, eta=1.0, decay=0.0)

    # Creates the \theta matrix 
    theta = model[corpus_ldac]
    theta_matrix = np.zeros((num_docs, num_topics))
    dcount = 0
    for theta_d in theta: 
        for theta_dt in theta_d: 
            theta_matrix[dcount, int(theta_dt[0])] = float(theta_dt[1])
        dcount += 1
    
    with codecs.open(theta_file, mode='w', encoding='utf-8') as fw:
        for i in range(0, num_docs):
            fw.write(str(paper_ids[i]) + u'|{')
            s = ''
            for j in range(0, num_topics):
                s += str(theta_matrix[i,j]) + u','
            fw.write(s.rstrip(u',') + u'}\n')
        
    # np.savetxt(theta_file, theta_matrix)
    
    print 'Number of documents: ', dcount
    
    topics = model.show_topics(topics=-1, topn=50, log=False, formatted=False)
    with codecs.open(topics_file, mode='w', encoding='utf-8') as fw:
        fw.write(u'topic_id|topic_words\n')
        for i in range(0, num_topics): 
            topic_words = u",".join(w[1] for w in topics[i])
            fw.write(str(i+1) + u"|{" + topic_words + u"}\n")
            
    
def create_corpus(papers_file, dictionary_file, ldac_file, en_sw_file):

    create_dictionary(en_sw_file, papers_file, dictionary_file)    
    print 'Created dictionary.'

    dictionary = corpora.Dictionary().load(dictionary_file)       
    corpus_memory_friendly = TextCorpus(dictionary, papers_file) # doesn't load the corpus into memory!
    corpora.BleiCorpus.serialize(ldac_file, corpus_memory_friendly, id2word=dictionary)
    print 'Created corpus.'
    

def run_lsi(dictionary_file, ldac_file, lsi_file, topics_file, num_topics, paper_ids):
   
   
    dictionary = corpora.Dictionary().load(dictionary_file) 
    corpus_ldac = corpora.BleiCorpus(fname=ldac_file, fname_vocab=(ldac_file + '.vocab'))  
    num_docs = len(corpus_ldac)
    
    '''
    Writes the corpus-documents TFIDF values into a file 
    '''
    
    tfidf_mdl = models.TfidfModel(corpus_ldac) 
    corpus_tfidf = tfidf_mdl[corpus_ldac]
    
    lsi = models.LsiModel(corpus_tfidf, id2word=dictionary, num_topics=num_topics)
    corpus_lsi = lsi[corpus_tfidf]
    
    
    lsi_matrix = np.zeros((num_docs, num_topics))
    row_count = 0
    for doc in corpus_lsi: 
        for each_tuple in doc:
            lsi_matrix[row_count, int(each_tuple[0])] = float(each_tuple[1])
        row_count += 1  
    
    with codecs.open(lsi_file, mode='w', encoding='utf-8') as fw:
        for i in range(0, num_docs):
            fw.write(str(paper_ids[i]) + u'|{')
            s = ''
            for j in range(0, num_topics):
                s += str(lsi_matrix[i,j]) + u','
            fw.write(s.rstrip(u',') + u'}\n')
        
                  
#    np.savetxt(lsi_file, lsi_matrix)
    
    print 'Number of documents: ', row_count
 
    topics = lsi.show_topics(num_topics=-1, num_words=50, log=False, formatted=False)
    with codecs.open(topics_file, mode='w', encoding='utf-8') as fw:
        fw.write(u'topic_id|topic_words\n')
        for i in range(0, num_topics): 
            topic_words = u",".join(w[1] for w in topics[i])
            fw.write(str(i+1) + u"|{" + topic_words + u"}\n")

if __name__ == '__main__':
    
    '''
    Creates a dictionary and corpus for the LDA algorithm 

    Created on: 
        June 13, 2012 
        
    '''
    
    
    # input files
    IN_DIR = '/home/cgrant/projects/paper/'
    OUT_DIR = '/home/cgrant/projects/paper/topics/'
    FILE_PREFIX = 'paper_noheader_nounicode'
    en_sw_file = 'en_stopwords'
    papers_file = IN_DIR + FILE_PREFIX + '.csv'
    dictionary_file = OUT_DIR + FILE_PREFIX + '.dict'
    ldac_file = OUT_DIR + FILE_PREFIX + '.ldac'
    theta_file = OUT_DIR + FILE_PREFIX + '.theta'
    lda_topics_file = OUT_DIR + FILE_PREFIX + '.lda_topic_words'
    lsi_topics_file = OUT_DIR + FILE_PREFIX + '.lsi_topic_words'
    lsi_file = OUT_DIR + FILE_PREFIX + '.lsi'
    num_topics = 35
    num_passes = 10
    
    # creates the corpus 
    if(not os.path.exists(dictionary_file) or not os.path.exists(ldac_file)):
        create_corpus(papers_file, dictionary_file, ldac_file, en_sw_file)
    
    paper_ids = list()
    for line in codecs.open(papers_file, mode='r', encoding='utf-8'):
        paper_ids.append(line.strip().split(u'|')[0])
    
    from time import time
    
    tm = time()
    
    # run LDA 
    run_lda(dictionary_file, ldac_file, theta_file, lda_topics_file, num_topics, num_passes, paper_ids)
    
    # run LSI  -- Lets stop this for a while
    # run_lsi(dictionary_file, ldac_file, lsi_file, lsi_topics_file, num_topics, paper_ids)   
    
    print '\n\nExecution time:', (time() - tm) 
    
    

