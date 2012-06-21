
### Installing on ubuntu

Install a few packages

	sudo apt-get install python-setuptool python-nltk python-tk python numpy python-matplotlib

	sudo easy_install -U gensim

Then download the corpus into nltk. We recommend downloading `all`.

	python -c "import nltk; nltk.download()"

### Running the topic modeling

Specify the `IN\_DIR` In the create\_topic\_models.py. This directory should
contain the paper.csv (is the file you are building from) and the stopwords i
file (`en\_sw\_file`). 

Specify the `OUT\_DIR` In the create\_topic\_models.py. This is where all data will go.

Create the correct `FILE\_PREFIX` for the paper.csv file.
*Do not include the `.csv`* The prefix is appended in the line `papers\_file`.
To remove the header you can do a sed 
command `sed 1,1d paper.csv > paper_noheader.csv`.

Choose a positive integer for `num\_topics` and `num_passes`. These values 
need to be adjusted depending on the data.

If you find the vocabulary size is too large, you can increase the 
value of `MIN\_FREQUENCY` in the `create\_topic\_models.py` file. 

It takes a long time to run so be patient.

### Reading the output

The file ending in `.theta` is the topic document distribution.

The file ending in `.lda_topic_words` is a list of the top words
for each topic. This can be used to help eyeball the accuracy.


